<?php

namespace App\Http\Controllers;

use App\Exports\GradesExport;
use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use App\Services\GradeService;
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

class GradeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly GradeService $gradeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.grade', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.grade', only: ['create', 'store']),
            new Middleware('can:edit.grade', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.grade', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('grades.index', [
            'academicYears' => $this->gradeService->academicYears(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->gradeService->query($request->only([
            'academic_year_id',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Grade $grade): string => sprintf(
                '<input type="checkbox" class="grade-row-check" value="%d">',
                $grade->id
            ))
            ->addColumn('academic_year', fn (Grade $grade): string => $grade->academicYear?->academic_year ?? '-')
            ->editColumn('is_active', fn (Grade $grade): string => sprintf(
                '<span class="%s">%s</span>',
                $grade->is_active ? 'status-green' : 'status-red',
                $grade->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Grade $grade): string => $this->actionButtons($grade))
            ->filterColumn('academic_year', function ($query, string $keyword): void {
                $query->whereHas('academicYear', function ($query) use ($keyword): void {
                    $query->where('academic_year', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('grades.form', [
            'grade' => new Grade([
                'code' => $this->gradeService->nextCode(),
                'is_active' => false,
            ]),
            'academicYears' => $this->gradeService->academicYears(),
        ]);
    }

    public function store(GradeRequest $request): RedirectResponse
    {
        $this->gradeService->create($request->validated());

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grade created successfully.');
    }

    public function edit(Grade $grade): View
    {
        return view('grades.form', [
            'grade' => $grade,
            'academicYears' => $this->gradeService->academicYears(),
        ]);
    }

    public function update(GradeRequest $request, Grade $grade): RedirectResponse
    {
        $this->gradeService->update($grade, $request->validated());

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Request $request, Grade $grade): JsonResponse|RedirectResponse
    {
        $this->gradeService->delete($grade);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Grade deleted successfully.',
            ]);
        }

        return redirect()
            ->route('grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    public function toggleStatus(Request $request, Grade $grade): JsonResponse|RedirectResponse
    {
        $grade = $this->gradeService->toggleStatus($grade);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Grade status updated successfully.',
                'is_active' => $grade->is_active,
                'status' => $grade->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Grade status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $grades = $this->selectedGrades($request);

        if ($grades->isEmpty()) {
            return back()->with('error', 'Select at least one grade to export.');
        }

        return Excel::download(new GradesExport($grades), 'grades.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $grades = $this->selectedGrades($request);

        if ($grades->isEmpty()) {
            return back()->with('error', 'Select at least one grade to export.');
        }

        return Pdf::loadView('grades.export-pdf', ['grades' => $grades])
            ->download('grades.pdf');
    }

    private function selectedGrades(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->gradeService->selectedForExport($ids);
    }

    private function actionButtons(Grade $grade): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.grade')) {
            $buttons .= view('grades.partials.toggle-status', compact('grade'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('grades.edit', $grade)
            );
        }

        if (request()->user()?->can('delete.grade')) {
            $buttons .= view('grades.partials.delete-button', compact('grade'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
