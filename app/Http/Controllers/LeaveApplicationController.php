<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveApplicationRequest;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Teacher;
use App\Models\TeacherLeaveBalance;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PrefixCodeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class LeaveApplicationController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.leave-application', only: ['index', 'data', 'show']),
            new Middleware('can:create.leave-application', only: ['create', 'store']),
            new Middleware('can:edit.leave-application', only: ['edit', 'update']),
            new Middleware('can:approve.leave-application', only: ['approve', 'reject']),
        ];
    }

    public function index(): View
    {
        return view('leave-applications.index', $this->options());
    }

    public function data(Request $request): JsonResponse
    {
        $query = LeaveApplication::query()
            ->with(['teacher', 'user', 'role', 'leaveType', 'appliedBy'])
            ->when($request->filled('leave_type_id'), fn($query) => $query->where('leave_type_id', $request->integer('leave_type_id')))
            ->when($request->filled('applicant_type'), fn($query) => $query->where('applicant_type', $request->string('applicant_type')))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('from_date'), fn($query) => $query->whereDate('from_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn($query) => $query->whereDate('to_date', '<=', $request->date('to_date')));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                if ($keyword = $request->input('search.value')) {
                    $query->where(function ($query) use ($keyword): void {
                        $query->where('application_no', 'like', "%{$keyword}%")
                            ->orWhere('reason', 'like', "%{$keyword}%")
                            ->orWhereHas('teacher', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('appliedBy', fn($q) => $q->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('leaveType', fn($q) => $q->where('leave_name', 'like', "%{$keyword}%"));
                    });
                }
            })
            ->editColumn('application_no', fn(LeaveApplication $leave): string => e($leave->application_no) . ($leave->submitted_by_applicant && ! $leave->admin_viewed_at ? ' <span class="badge bg-danger new-leave-badge">New</span>' : ''))
            ->addColumn('applicant', fn(LeaveApplication $leave): string => e($leave->applicant_name))
            ->addColumn('applied_by_name', fn(LeaveApplication $leave): string => e($leave->appliedBy?->name ?? '-'))
            ->addColumn('user_type', fn(LeaveApplication $leave): string => $leave->applicant_type === 'user' ? e($leave->role?->name ?? 'User') : 'Teacher')
            ->addColumn('leave_type', fn(LeaveApplication $leave): string => e($leave->leaveType?->leave_name ?? '-'))
            ->editColumn('applied_date', fn(LeaveApplication $leave): string => $leave->applied_date?->format('d M Y') ?? '-')
            ->editColumn('from_date', fn(LeaveApplication $leave): string => $leave->from_date?->format('d M Y') ?? '-')
            ->editColumn('to_date', fn(LeaveApplication $leave): string => $leave->to_date?->format('d M Y') ?? '-')
            ->editColumn('status', fn(LeaveApplication $leave): string => $this->statusBadge($leave->status))
            ->addColumn('actions', fn(LeaveApplication $leave): string => $this->actions($leave))
            ->rawColumns(['application_no', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('leave-applications.form', [
            'leave' => new LeaveApplication([
                'application_no' => $this->prefixCodeService->next('leave_application', LeaveApplication::class, 'application_no'),
                'applied_date' => now(),
                'status' => 'Pending',
            ]),
            ...$this->options(),
        ]);
    }

    public function store(LeaveApplicationRequest $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $data['application_no'] = $this->prefixCodeService->next('leave_application', LeaveApplication::class, 'application_no');
        $data['applied_date'] = now()->toDateString();
        $data['applied_by'] = auth()->id();
        $data['status'] = $leaveType->requires_approval ? 'Pending' : 'Approved';
        $data['approved_by'] = $leaveType->requires_approval ? null : auth()->id();
        $data['approved_at'] = $leaveType->requires_approval ? null : now();

        $leave = DB::transaction(function () use ($data, $leaveType): LeaveApplication {
            $leave = LeaveApplication::create($data);
            if ($leave->status === 'Approved') {
                $this->consumeBalance($leave, $leaveType);
            }
            return $leave;
        });

        ActivityLogService::log('Leave', 'Create', $leave->id, 'Leave application created.');

        return redirect()->route('leave-applications.index')->with(
            'success',
            $leave->status === 'Approved'
                ? 'Leave application saved and automatically approved.'
                : 'Leave application submitted for approval.',
        );
    }

    public function edit(LeaveApplication $leave): View
    {
        abort_if($leave->isProcessed(), 422, 'Approved or rejected leave applications cannot be edited.');

        return view('leave-applications.form', ['leave' => $leave, ...$this->options()]);
    }

    public function update(LeaveApplicationRequest $request, LeaveApplication $leave): RedirectResponse
    {
        abort_if($leave->isProcessed(), 422, 'Approved or rejected leave applications cannot be edited.');
        $leave->update($this->validatedPayload($request, $leave));
        ActivityLogService::log('Leave', 'Update', $leave->id, 'Pending leave application updated.');

        return redirect()->route('leave-applications.index')->with('success', 'Leave application updated successfully.');
    }

    public function show(LeaveApplication $leave): View
    {
        if ($leave->submitted_by_applicant && ! $leave->admin_viewed_at) {
            $leave->forceFill(['admin_viewed_at' => now()])->save();
        }
        return view('leave-applications.show', ['leave' => $leave->load(['teacher', 'user', 'role', 'leaveType', 'appliedBy', 'approver'])]);
    }

    public function approve(Request $request, LeaveApplication $leave): RedirectResponse
    {
        $request->validate(['remarks' => ['nullable', 'string', 'max:1000']]);
        $leave->load('leaveType');
        abort_if($leave->status !== 'Pending', 422, 'This leave application has already been processed.');
        abort_unless($leave->leaveType?->requires_approval, 422, 'This leave type does not require approval.');

        DB::transaction(function () use ($leave, $request): void {
            $this->consumeBalance($leave, $leave->leaveType);
            $leave->update([
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $request->remarks,
            ]);
        });

        return back()->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, LeaveApplication $leave): RedirectResponse
    {
        $request->validate(['remarks' => ['required', 'string', 'max:1000']]);
        $leave->load('leaveType');
        abort_if($leave->status !== 'Pending', 422, 'This leave application has already been processed.');
        abort_unless($leave->leaveType?->requires_approval, 422, 'This leave type does not require approval.');
        $leave->update([
            'status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', 'Leave rejected successfully.');
    }

    private function validatedPayload(LeaveApplicationRequest $request, ?LeaveApplication $current = null): array
    {
        $data = $request->validated();
        $leaveType = LeaveType::findOrFail($data['leave_type_id']);
        $from = Carbon::parse($data['from_date'])->startOfDay();
        $to = Carbon::parse($data['to_date'])->startOfDay();
        $days = $data['is_half_day'] ? 0.5 : $from->diffInDays($to) + 1;

        if ($data['is_half_day'] && (! $leaveType->allow_half_day || ! $from->equalTo($to))) {
            throw ValidationException::withMessages(['is_half_day' => 'Half day is not allowed for this leave type or date range.']);
        }
        $maxPerRequest = (int) ($leaveType->max_leave_days_per_request ?? $leaveType->total_days ?? 1);
        if ($days > $maxPerRequest) {
            throw ValidationException::withMessages(['to_date' => 'Maximum ' . $maxPerRequest . ' leave day(s) are allowed per request.']);
        }
        if ($from->lt(now()->startOfDay()->addDays((int) $leaveType->advance_notice_days))) {
            throw ValidationException::withMessages(['from_date' => 'This leave type requires ' . $leaveType->advance_notice_days . ' day(s) advance notice.']);
        }

        $this->assertApplicantAndLeaveType($data, $leaveType);
        $used = LeaveApplication::query()
            ->where('leave_type_id', $leaveType->id)
            ->whereYear('from_date', $from->year)
            ->whereIn('status', ['Pending', 'Approved'])
            ->when($data['applicant_type'] === 'teacher', fn($q) => $q->where('teacher_id', $data['teacher_id']), fn($q) => $q->where('user_id', $data['user_id']))
            ->when($current, fn($q) => $q->where('id', '!=', $current->id))
            ->sum('days');
        $maxPerYear = (int) ($leaveType->max_leaves_per_year ?? $leaveType->total_days ?? 0);
        if (((float) $used + $days) > $maxPerYear) {
            throw ValidationException::withMessages(['leave_type_id' => 'The maximum leaves allowed for this year would be exceeded.']);
        }

        return [
            'applicant_type' => $data['applicant_type'],
            'teacher_id' => $data['teacher_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'role_id' => $data['role_id'] ?? null,
            'leave_type_id' => $leaveType->id,
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'days' => $days,
            'is_half_day' => $data['is_half_day'],
            'reason' => $data['reason'],
        ];
    }

    private function assertApplicantAndLeaveType(array $data, LeaveType $leaveType): void
    {
        if ($data['applicant_type'] === 'teacher') {
            $teacher = Teacher::findOrFail($data['teacher_id']);
            if (($leaveType->applicable_for ?? 'all') === 'role') {
                throw ValidationException::withMessages(['leave_type_id' => 'This leave type is not applicable to teachers.']);
            }
            if (($leaveType->gender_specific ?? 'all') !== 'all' && strtolower((string) $teacher->gender) !== $leaveType->gender_specific) {
                throw ValidationException::withMessages(['leave_type_id' => 'This leave type is not applicable to the selected teacher’s gender.']);
            }
            return;
        }

        $user = User::findOrFail($data['user_id']);
        if ((int) $user->role_id !== (int) $data['role_id']) {
            throw ValidationException::withMessages(['applicant_id' => 'The selected user does not belong to this role.']);
        }
        if ($leaveType->applicable_for === 'teachers' || ($leaveType->applicable_for === 'role' && (int) $leaveType->role_id !== (int) $data['role_id'])) {
            throw ValidationException::withMessages(['leave_type_id' => 'This leave type is not applicable to the selected role.']);
        }
        if (($leaveType->gender_specific ?? 'all') !== 'all') {
            throw ValidationException::withMessages(['leave_type_id' => 'Gender-specific leave types can only be assigned where gender information is available.']);
        }
    }

    private function consumeBalance(LeaveApplication $leave, LeaveType $leaveType): void
    {
        if ($leave->applicant_type !== 'teacher' || $leaveType->leave_type === 'unpaid') {
            return;
        }
        $balance = TeacherLeaveBalance::query()
            ->where('teacher_id', $leave->teacher_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->lockForUpdate()
            ->first();
        if ($balance && (float) $balance->remaining_days < (float) $leave->days) {
            throw ValidationException::withMessages(['leave_type_id' => 'Insufficient leave balance.']);
        }
        if ($balance) {
            $balance->increment('used_days', $leave->days);
            $balance->decrement('remaining_days', $leave->days);
        }
    }

    private function options(): array
    {
        return [
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'gender']),
            'users' => User::query()->where('is_active', true)->with('role')->orderBy('name')->get(['id', 'name', 'role_id']),
            'roles' => Role::query()->where('name', '!=', 'admin')->orderBy('name')->get(['id', 'name']),
            'leaveTypes' => LeaveType::query()->where('status', true)->with('role')->orderBy('leave_name')->get(),
            'statuses' => LeaveApplication::STATUSES,
        ];
    }

    private function statusBadge(string $status): string
    {
        $class = match ($status) {
            'Approved' => 'status-green',
            'Rejected' => 'status-red',
            default => 'status-orange',
        };
        return '<span class="' . $class . '">' . e($status) . '</span>';
    }

    private function actions(LeaveApplication $leave): string
    {
        $buttons = sprintf('<a href="%s" class="btn-edit" title="View"><i class="fa-solid fa-eye"></i></a>', route('leave-applications.show', $leave));
        if (! $leave->isProcessed() && request()->user()?->can('edit.leave-application')) {
            $buttons .= sprintf('<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>', route('leave-applications.edit', $leave));
        }
        if (
            $leave->status === 'Pending'
            && $leave->leaveType?->requires_approval
            && request()->user()?->can('approve.leave-application')
        ) {
            $buttons .= sprintf(
                '<button type="button" class="btn btn-success btn-sm leave-decision-btn" data-decision="approve" data-application="%s" data-action-url="%s" title="Approve"><i class="fa-solid fa-check me-1"></i>Approve</button>',
                e($leave->application_no),
                route('leave-applications.approve', $leave),
            );
            $buttons .= sprintf(
                '<button type="button" class="btn btn-danger btn-sm leave-decision-btn" data-decision="reject" data-application="%s" data-action-url="%s" title="Reject"><i class="fa-solid fa-xmark me-1"></i>Reject</button>',
                e($leave->application_no),
                route('leave-applications.reject', $leave),
            );
        }
        return '<div class="action-btns">' . $buttons . '</div>';
    }
}
