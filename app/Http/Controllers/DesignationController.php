<?php

namespace App\Http\Controllers;

use App\Exports\DesignationsExport;
use App\Http\Requests\DesignationRequest;
use App\Models\Designation;
use App\Services\DesignationService;
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

class DesignationController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DesignationService $designationService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.designation', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.designation', only: ['create', 'store']),
            new Middleware('can:edit.designation', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.designation', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('designations.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->designationService->query($request->only([
            'designation_name',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Designation $designation): string => sprintf(
                '<input type="checkbox" class="designation-row-check" value="%d">',
                $designation->id
            ))
            ->editColumn('is_active', fn (Designation $designation): string => sprintf(
                '<span class="%s">%s</span>',
                $designation->is_active ? 'status-green' : 'status-red',
                $designation->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Designation $designation): string => $this->actionButtons($designation))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('designations.form', [
            'designation' => new Designation([
                'code' => $this->designationService->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(DesignationRequest $request): RedirectResponse
    {
        $this->designationService->create($request->validated());

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation): View
    {
        return view('designations.form', [
            'designation' => $designation,
        ]);
    }

    public function update(DesignationRequest $request, Designation $designation): RedirectResponse
    {
        $this->designationService->update($designation, $request->validated());

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Request $request, Designation $designation): JsonResponse|RedirectResponse
    {
        $this->designationService->delete($designation);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Designation deleted successfully.',
            ]);
        }

        return redirect()
            ->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }

    public function toggleStatus(Request $request, Designation $designation): JsonResponse|RedirectResponse
    {
        $designation = $this->designationService->toggleStatus($designation);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Designation status updated successfully.',
                'is_active' => $designation->is_active,
                'status' => $designation->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Designation status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $designations = $this->selectedDesignations($request);

        if ($designations->isEmpty()) {
            return back()->with('error', 'Select at least one designation to export.');
        }

        return Excel::download(new DesignationsExport($designations), 'designations.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $designations = $this->selectedDesignations($request);

        if ($designations->isEmpty()) {
            return back()->with('error', 'Select at least one designation to export.');
        }

        return Pdf::loadView('designations.export-pdf', ['designations' => $designations])
            ->download('designations.pdf');
    }

    private function selectedDesignations(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->designationService->selectedForExport($ids);
    }

    private function actionButtons(Designation $designation): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.designation')) {
            $buttons .= view('designations.partials.toggle-status', compact('designation'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('designations.edit', $designation)
            );
        }

        if (request()->user()?->can('delete.designation')) {
            $buttons .= view('designations.partials.delete-button', compact('designation'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }

}
