<?php

namespace App\Http\Controllers;

use App\Exports\LeaveTypesExport;
use App\Http\Requests\LeaveTypeRequest;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class LeaveTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly LeaveTypeService $leaveTypeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.leave-type', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.leave-type', only: ['create', 'store']),
            new Middleware('can:edit.leave-type', only: ['edit', 'update']),
            new Middleware('can:delete.leave-type', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('leave-types.index', $this->options());
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->leaveTypeService->query($request->only(['leave_type', 'applicable_for', 'status']));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if ($keyword) {
                    $query->where(function ($query) use ($keyword): void {
                        $query->where('code', 'like', "%{$keyword}%")
                            ->orWhere('leave_name', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%")
                            ->orWhereHas('role', fn ($roleQuery) => $roleQuery->where('name', 'like', "%{$keyword}%"));
                    });
                }
            })
            ->addColumn('select', fn (LeaveType $leaveType): string => sprintf(
                '<input type="checkbox" class="leave-type-row-check" value="%d">',
                $leaveType->id,
            ))
            ->editColumn('leave_type', fn (LeaveType $leaveType): string => LeaveType::LEAVE_TYPES[$leaveType->leave_type]
                ?? ($leaveType->is_lop ? 'Unpaid' : 'Paid'))
            ->editColumn('max_leaves_per_year', fn (LeaveType $leaveType): int => (int) ($leaveType->max_leaves_per_year ?? $leaveType->total_days ?? 0))
            ->addColumn('applicable_for_text', fn (LeaveType $leaveType): string => $leaveType->applicable_for_text)
            ->editColumn('carry_forward_allowed', fn (LeaveType $leaveType): string => $this->yesNo($leaveType->carry_forward_allowed))
            ->editColumn('gender_specific', fn (LeaveType $leaveType): string => $leaveType->gender_specific
                ? ucfirst($leaveType->gender_specific)
                : '-')
            ->editColumn('max_leave_days_per_request', fn (LeaveType $leaveType): string => (string) (
                $leaveType->max_leave_days_per_request ?? max(1, (int) ($leaveType->total_days ?? 1))
            ))
            ->editColumn('status', fn (LeaveType $leaveType): string => sprintf(
                '<span class="%s">%s</span>',
                $leaveType->status ? 'status-green' : 'status-red',
                $leaveType->status ? 'Active' : 'Inactive',
            ))
            ->addColumn('actions', fn (LeaveType $leaveType): string => $this->actions($leaveType))
            ->rawColumns(['select', 'carry_forward_allowed', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('leave-types.form', [
            'leaveType' => new LeaveType([
                'code' => $this->leaveTypeService->nextCode(),
                'status' => true,
                'carry_forward_allowed' => false,
                'allow_half_day' => false,
                'requires_approval' => true,
                'encashment_allowed' => false,
            ]),
            ...$this->options(),
        ]);
    }

    public function store(LeaveTypeRequest $request): RedirectResponse
    {
        $this->leaveTypeService->create($request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave-types.form', ['leaveType' => $leaveType, ...$this->options()]);
    }

    public function update(LeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $this->leaveTypeService->update($leaveType, $request->validated());

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy(Request $request, LeaveType $leaveType): JsonResponse|RedirectResponse
    {
        if ($leaveType->applications()->exists() || $leaveType->balances()->exists()) {
            $message = 'This leave type is already in use and cannot be deleted.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $this->leaveTypeService->delete($leaveType);

        return $request->expectsJson()
            ? response()->json(['message' => 'Leave type deleted successfully.'])
            : redirect()->route('leave-types.index')->with('success', 'Leave type deleted successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $leaveTypes = $this->selected($request);

        return $leaveTypes->isEmpty()
            ? back()->with('error', 'Select at least one leave type to export.')
            : Excel::download(new LeaveTypesExport($leaveTypes), 'leave-types.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $leaveTypes = $this->selected($request);

        return $leaveTypes->isEmpty()
            ? back()->with('error', 'Select at least one leave type to export.')
            : Pdf::loadView('leave-types.export-pdf', compact('leaveTypes'))->setPaper('a4', 'landscape')->download('leave-types.pdf');
    }

    private function selected(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))->map(fn ($id) => (int) $id)->filter()->unique()->all();

        return $this->leaveTypeService->selectedForExport($ids);
    }

    private function options(): array
    {
        return [
            'leaveTypes' => LeaveType::LEAVE_TYPES,
            'applicableForOptions' => LeaveType::APPLICABLE_FOR,
            'genders' => LeaveType::GENDERS,
            'statuses' => LeaveType::STATUSES,
            'roles' => $this->leaveTypeService->roles(),
        ];
    }

    private function yesNo(?bool $value): string
    {
        return sprintf('<span class="%s">%s</span>', $value ? 'status-green' : 'status-red', $value ? 'Yes' : 'No');
    }

    private function actions(LeaveType $leaveType): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.leave-type')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('leave-types.edit', $leaveType),
            );
        }

        if (request()->user()?->can('delete.leave-type')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-delete leave-type-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button>',
                route('leave-types.destroy', $leaveType),
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
