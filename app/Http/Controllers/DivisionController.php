<?php

namespace App\Http\Controllers;

use App\Exports\DivisionsExport;
use App\Http\Requests\DivisionRequest;
use App\Models\Division;
use App\Services\DivisionService;
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

class DivisionController extends Controller implements HasMiddleware
{
    public function __construct(private readonly DivisionService $divisionService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.division', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.division', only: ['create', 'store']),
            new Middleware('can:edit.division', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.division', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('divisions.index', [
            'grades' => $this->divisionService->grades(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->divisionService->query($request->only([
            'grade_id',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Division $division): string => sprintf(
                '<input type="checkbox" class="division-row-check" value="%d">',
                $division->id
            ))
            ->addColumn('grade', fn (Division $division): string => $this->gradeWithYear($division))
            ->editColumn('class_teacher', fn (Division $division): string => $division->class_teacher ?? '-')
            ->editColumn('room_number', fn (Division $division): string => $division->room_number ? (string) $division->room_number : '-')
            ->editColumn('is_active', fn (Division $division): string => sprintf(
                '<span class="%s">%s</span>',
                $division->is_active ? 'status-green' : 'status-red',
                $division->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Division $division): string => $this->actionButtons($division))
            ->filterColumn('grade', function ($query, string $keyword): void {
                $query->whereHas('grade', function ($query) use ($keyword): void {
                    $query->where('grade', 'like', "%{$keyword}%")
                        ->orWhereHas('academicYear', function ($query) use ($keyword): void {
                            $query->where('academic_year', 'like', "%{$keyword}%");
                        });
                });
            })
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('divisions.form', [
            'division' => new Division([
                'code' => $this->divisionService->nextCode(),
                'is_active' => false,
            ]),
            'grades' => $this->divisionService->grades(),
        ]);
    }

    public function store(DivisionRequest $request): RedirectResponse
    {
        $this->divisionService->create($request->validated());

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division created successfully.');
    }

    public function edit(Division $division): View
    {
        return view('divisions.form', [
            'division' => $division,
            'grades' => $this->divisionService->grades(),
        ]);
    }

    public function update(DivisionRequest $request, Division $division): RedirectResponse
    {
        $this->divisionService->update($division, $request->validated());

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    public function destroy(Request $request, Division $division): JsonResponse|RedirectResponse
    {
        $this->divisionService->delete($division);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Division deleted successfully.',
            ]);
        }

        return redirect()
            ->route('divisions.index')
            ->with('success', 'Division deleted successfully.');
    }

    public function toggleStatus(Request $request, Division $division): JsonResponse|RedirectResponse
    {
        $division = $this->divisionService->toggleStatus($division);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Division status updated successfully.',
                'is_active' => $division->is_active,
                'status' => $division->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Division status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $divisions = $this->selectedDivisions($request);

        if ($divisions->isEmpty()) {
            return back()->with('error', 'Select at least one division to export.');
        }

        return Excel::download(new DivisionsExport($divisions), 'divisions.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $divisions = $this->selectedDivisions($request);

        if ($divisions->isEmpty()) {
            return back()->with('error', 'Select at least one division to export.');
        }

        return Pdf::loadView('divisions.export-pdf', ['divisions' => $divisions])
            ->download('divisions.pdf');
    }

    private function selectedDivisions(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->divisionService->selectedForExport($ids);
    }

    private function actionButtons(Division $division): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.division')) {
            $buttons .= view('divisions.partials.toggle-status', compact('division'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('divisions.edit', $division)
            );
        }

        if (request()->user()?->can('delete.division')) {
            $buttons .= view('divisions.partials.delete-button', compact('division'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }

    private function gradeWithYear(Division $division): string
    {
        if (! $division->grade) {
            return '-';
        }

        $academicYear = $division->grade->academicYear?->academic_year;

        return $academicYear
            ? $division->grade->grade.' - '.$academicYear
            : $division->grade->grade;
    }
}
