<?php

namespace App\Http\Controllers;

use App\Exports\DepartmentsExport;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DepartmentService $departmentService) {}

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware(PermissionMiddleware::using('view.department'), ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware(PermissionMiddleware::using('create.department'), ['create', 'store']),
            new Middleware(PermissionMiddleware::using('edit.department'), ['edit', 'update', 'toggleStatus']),
            new Middleware(PermissionMiddleware::using('delete.department'), ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('departments.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->departmentService->query($request->only([
            'department_name',
            'department_head',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn(Department $department): string => sprintf(
                '<input type="checkbox" class="department-row-check" value="%d">',
                $department->id
            ))
            ->editColumn('department_head', fn(Department $department): string => $department->department_head ?? '-')
            ->editColumn('is_active', fn(Department $department): string => sprintf(
                '<span class="%s">%s</span>',
                $department->is_active ? 'status-green' : 'status-red',
                $department->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn(Department $department): string => $this->actionButtons($department))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('departments.form', [
            'department' => new Department([
                'department_code' => $this->departmentService->nextDepartmentCode(),
                'teacher_count' => 0,
                'display_order' => 0,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->create($request->validated());

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): View
    {
        return view('departments.form', [
            'department' => $department,
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departmentService->update($department, $request->validated());

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request, Department $department): JsonResponse|RedirectResponse
    {
        $this->departmentService->delete($department);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Department deleted successfully.',
            ]);
        }

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    public function toggleStatus(Request $request, Department $department): JsonResponse|RedirectResponse
    {
        $department = $this->departmentService->toggleStatus($department);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Department status updated successfully.',
                'is_active' => $department->is_active,
                'status' => $department->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Department status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $departments = $this->selectedDepartments($request);

        if ($departments->isEmpty()) {
            return back()->with('error', 'Select at least one department to export.');
        }

        return Excel::download(new DepartmentsExport($departments), 'departments.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $departments = $this->selectedDepartments($request);

        if ($departments->isEmpty()) {
            return back()->with('error', 'Select at least one department to export.');
        }

        return Pdf::loadView('departments.export-pdf', ['departments' => $departments])
            ->download('departments.pdf');
    }

    private function selectedDepartments(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->departmentService->selectedForExport($ids);
    }

    private function actionButtons(Department $department): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.department')) {
            $buttons .= view('departments.partials.toggle-status', compact('department'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('departments.edit', $department)
            );
        }

        if (request()->user()?->can('delete.department')) {
            $buttons .= view('departments.partials.delete-button', compact('department'))->render();
        }

        return '<div class="action-btns">' . $buttons . '</div>';
    }
}
