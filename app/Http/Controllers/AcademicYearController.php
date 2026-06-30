<?php

namespace App\Http\Controllers;

use App\Exports\AcademicYearsExport;
use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
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

class AcademicYearController extends Controller implements HasMiddleware
{
    public function __construct(private readonly AcademicYearService $academicYearService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.academic-year', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.academic-year', only: ['create', 'store']),
            new Middleware('can:edit.academic-year', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.academic-year', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('academic-years.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->academicYearService->query($request->only([
            'academic_year',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (AcademicYear $academicYear): string => sprintf(
                '<input type="checkbox" class="academic-year-row-check" value="%d">',
                $academicYear->id
            ))
            ->editColumn('start_date', fn (AcademicYear $academicYear): string => $academicYear->start_date?->format('d M Y') ?? '-')
            ->editColumn('end_date', fn (AcademicYear $academicYear): string => $academicYear->end_date?->format('d M Y') ?? '-')
            ->editColumn('is_active', fn (AcademicYear $academicYear): string => sprintf(
                '<span class="%s">%s</span>',
                $academicYear->is_active ? 'status-green' : 'status-red',
                $academicYear->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (AcademicYear $academicYear): string => $this->actionButtons($academicYear))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('academic-years.form', [
            'academicYear' => new AcademicYear([
                'code' => $this->academicYearService->nextCode(),
                'is_active' => false,
            ]),
        ]);
    }

    public function store(AcademicYearRequest $request): RedirectResponse
    {
        $this->academicYearService->create($request->validated());

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic year created successfully.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('academic-years.form', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->academicYearService->update($academicYear, $request->validated());

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic year updated successfully.');
    }

    public function destroy(Request $request, AcademicYear $academicYear): JsonResponse|RedirectResponse
    {
        $this->academicYearService->delete($academicYear);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Academic year deleted successfully.',
            ]);
        }

        return redirect()
            ->route('academic-years.index')
            ->with('success', 'Academic year deleted successfully.');
    }

    public function toggleStatus(Request $request, AcademicYear $academicYear): JsonResponse|RedirectResponse
    {
        $academicYear = $this->academicYearService->toggleStatus($academicYear);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Academic year status updated successfully.',
                'is_active' => $academicYear->is_active,
                'status' => $academicYear->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Academic year status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $academicYears = $this->selectedAcademicYears($request);

        if ($academicYears->isEmpty()) {
            return back()->with('error', 'Select at least one academic year to export.');
        }

        return Excel::download(new AcademicYearsExport($academicYears), 'academic-years.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $academicYears = $this->selectedAcademicYears($request);

        if ($academicYears->isEmpty()) {
            return back()->with('error', 'Select at least one academic year to export.');
        }

        return Pdf::loadView('academic-years.export-pdf', ['academicYears' => $academicYears])
            ->download('academic-years.pdf');
    }

    private function selectedAcademicYears(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->academicYearService->selectedForExport($ids);
    }

    private function actionButtons(AcademicYear $academicYear): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.academic-year')) {
            $buttons .= view('academic-years.partials.toggle-status', compact('academicYear'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('academic-years.edit', $academicYear)
            );
        }

        if (request()->user()?->can('delete.academic-year')) {
            $buttons .= view('academic-years.partials.delete-button', compact('academicYear'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
