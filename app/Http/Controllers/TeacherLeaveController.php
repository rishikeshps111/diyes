<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\TeacherLeaveBalance;
use App\Services\PrefixCodeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherLeaveController extends Controller
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function index(Request $request): View
    {
        $this->teacher($request);

        return view('teachers.leave.index');
    }

    public function data(Request $request): JsonResponse
    {
        $teacher = $this->teacher($request);
        $query = LeaveApplication::query()
            ->with('leaveType')
            ->where('teacher_id', $teacher->id);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                if ($keyword = $request->input('search.value')) {
                    $query->where(function ($query) use ($keyword): void {
                        $query->where('application_no', 'like', "%{$keyword}%")
                            ->orWhere('reason', 'like', "%{$keyword}%")
                            ->orWhere('status', 'like', "%{$keyword}%")
                            ->orWhereHas('leaveType', fn ($leaveTypeQuery) => $leaveTypeQuery->where('leave_name', 'like', "%{$keyword}%"));
                    });
                }
            })
            ->addColumn('leave_type', fn (LeaveApplication $leave): string => e($leave->leaveType?->leave_name ?? '-'))
            ->editColumn('from_date', fn (LeaveApplication $leave): string => $leave->from_date?->format('d M Y') ?? '-')
            ->editColumn('to_date', fn (LeaveApplication $leave): string => $leave->to_date?->format('d M Y') ?? '-')
            ->editColumn('applied_date', fn (LeaveApplication $leave): string => $leave->applied_date?->format('d M Y') ?? '-')
            ->editColumn('status', function (LeaveApplication $leave): string {
                $class = match ($leave->status) {
                    'Approved' => 'status-green',
                    'Rejected' => 'status-red',
                    default => 'status-orange',
                };
                return '<span class="'.$class.'">'.e($leave->status).'</span>';
            })
            ->addColumn('actions', function (LeaveApplication $leave): string {
                $buttons = '<a href="'.route('teacher.leave.show', $leave).'" class="btn-edit" title="View"><i class="fa-solid fa-eye"></i></a>';
                if ($leave->status === 'Pending') {
                    $buttons .= '<a href="'.route('teacher.leave.edit', $leave).'" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>';
                    $buttons .= '<form method="POST" action="'.route('teacher.leave.cancel', $leave).'" class="d-inline teacher-leave-cancel-form">'
                        .csrf_field().method_field('DELETE')
                        .'<button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark me-1"></i>Cancel</button></form>';
                }
                return '<div class="action-btns">'.$buttons.'</div>';
            })
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        $teacher = $this->teacher($request);
        $leaveTypes = LeaveType::query()->where('status', true)
            ->where(fn ($query) => $query->where('applicable_for', 'all')->orWhere('applicable_for', 'teachers'))
            ->where(fn ($query) => $query->where('gender_specific', 'all')->orWhere('gender_specific', strtolower($teacher->gender)))
            ->orderBy('leave_name')->get();

        return view('teachers.leave.create', [
            'leave' => new LeaveApplication(),
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->teacher($request);
        [$data, $leaveType, $from, $to, $days] = $this->validatedLeave($request, $teacher);

        $status = $leaveType->requires_approval ? 'Pending' : 'Approved';
        LeaveApplication::create([
            'application_no' => $this->prefixCodeService->next('leave_application', LeaveApplication::class, 'application_no'),
            'applied_date' => today(),
            'applied_by' => $request->user()->id,
            'submitted_by_applicant' => true,
            'applicant_type' => 'teacher',
            'teacher_id' => $teacher->id,
            'leave_type_id' => $leaveType->id,
            'from_date' => $from,
            'to_date' => $to,
            'days' => $days,
            'is_half_day' => $request->boolean('is_half_day'),
            'reason' => $data['reason'],
            'status' => $status,
            'approved_by' => $status === 'Approved' ? $request->user()->id : null,
            'approved_at' => $status === 'Approved' ? now() : null,
        ]);

        return redirect()->route('teacher.leave.index')->with('success', $status === 'Approved' ? 'Leave applied and automatically approved.' : 'Leave applied successfully.');
    }

    public function show(Request $request, LeaveApplication $leave): View
    {
        $this->authorizeTeacherLeave($request, $leave);

        return view('leave-applications.show', [
            'leave' => $leave->load(['teacher', 'leaveType', 'appliedBy', 'approver']),
            'teacherView' => true,
        ]);
    }

    public function edit(Request $request, LeaveApplication $leave): View
    {
        $teacher = $this->authorizeTeacherLeave($request, $leave);
        abort_if($leave->status !== 'Pending', 422, 'Only pending leave applications can be edited.');

        $leaveTypes = LeaveType::query()->where('status', true)
            ->where(fn ($query) => $query->where('applicable_for', 'all')->orWhere('applicable_for', 'teachers'))
            ->where(fn ($query) => $query->where('gender_specific', 'all')->orWhere('gender_specific', strtolower($teacher->gender)))
            ->orderBy('leave_name')->get();

        return view('teachers.leave.create', compact('leave', 'leaveTypes'));
    }

    public function update(Request $request, LeaveApplication $leave): RedirectResponse
    {
        $teacher = $this->authorizeTeacherLeave($request, $leave);
        abort_if($leave->status !== 'Pending', 422, 'Only pending leave applications can be edited.');
        [$data, $leaveType, $from, $to, $days] = $this->validatedLeave($request, $teacher, $leave);
        $leave->update([
            'leave_type_id' => $leaveType->id,
            'from_date' => $from,
            'to_date' => $to,
            'days' => $days,
            'is_half_day' => $request->boolean('is_half_day'),
            'reason' => $data['reason'],
            'status' => $leaveType->requires_approval ? 'Pending' : 'Approved',
            'approved_by' => $leaveType->requires_approval ? null : $request->user()->id,
            'approved_at' => $leaveType->requires_approval ? null : now(),
        ]);

        return redirect()->route('teacher.leave.index')->with('success', 'Leave application updated successfully.');
    }

    public function cancel(Request $request, LeaveApplication $leave): RedirectResponse
    {
        $this->authorizeTeacherLeave($request, $leave);
        abort_if($leave->status !== 'Pending', 422, 'Only pending leave can be cancelled.');
        $leave->delete();

        return back()->with('success', 'Leave application cancelled.');
    }

    public function getLeaveBalance(Request $request, LeaveType $leaveType): JsonResponse
    {
        $teacher = $this->teacher($request);
        $balance = TeacherLeaveBalance::query()->where('teacher_id', $teacher->id)
            ->where('leave_type_id', $leaveType->id)->first();
        $used = LeaveApplication::query()->where('teacher_id', $teacher->id)
            ->where('leave_type_id', $leaveType->id)->where('status', 'Approved')
            ->whereYear('from_date', now()->year)->sum('days');

        return response()->json(['remaining_days' => $balance?->remaining_days ?? max(0, $leaveType->max_leaves_per_year - $used)]);
    }

    private function teacher(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'This account is not linked to a teacher.');

        return $teacher;
    }

    private function authorizeTeacherLeave(Request $request, LeaveApplication $leave)
    {
        $teacher = $this->teacher($request);
        abort_unless((int) $leave->teacher_id === (int) $teacher->id, 403);

        return $teacher;
    }

    private function validatedLeave(Request $request, $teacher, ?LeaveApplication $current = null): array
    {
        $data = $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'is_half_day' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $leaveType = LeaveType::query()->where('status', true)->findOrFail($data['leave_type_id']);
        abort_unless(in_array($leaveType->applicable_for, ['all', 'teachers'], true), 422);
        abort_unless($leaveType->gender_specific === 'all' || $leaveType->gender_specific === strtolower($teacher->gender), 422);
        $from = Carbon::parse($data['from_date']);
        $to = Carbon::parse($data['to_date']);
        $days = $request->boolean('is_half_day') ? .5 : $from->diffInDays($to) + 1;
        if ($request->boolean('is_half_day') && (! $leaveType->allow_half_day || ! $from->isSameDay($to))) {
            throw ValidationException::withMessages(['is_half_day' => 'Half day is not allowed for this request.']);
        }
        if ($days > $leaveType->max_leave_days_per_request) {
            throw ValidationException::withMessages(['to_date' => "Maximum {$leaveType->max_leave_days_per_request} day(s) are allowed per request."]);
        }
        if (today()->diffInDays($from, false) < $leaveType->advance_notice_days) {
            throw ValidationException::withMessages(['from_date' => "{$leaveType->advance_notice_days} day(s) advance notice is required."]);
        }
        $used = (float) LeaveApplication::query()->where('teacher_id', $teacher->id)
            ->where('leave_type_id', $leaveType->id)->whereIn('status', ['Pending', 'Approved'])
            ->whereYear('from_date', $from->year)
            ->when($current, fn ($query) => $query->where('id', '!=', $current->id))
            ->sum('days');
        if ($leaveType->leave_type === 'paid' && $used + $days > $leaveType->max_leaves_per_year) {
            throw ValidationException::withMessages(['leave_type_id' => 'The annual leave limit would be exceeded.']);
        }

        return [$data, $leaveType, $from, $to, $days];
    }
}
