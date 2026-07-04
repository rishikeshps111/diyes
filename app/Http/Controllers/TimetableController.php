<?php

namespace App\Http\Controllers;

use App\Exports\TimetablesExport;
use App\Http\Requests\TimetableRequest;
use App\Models\Timetable;
use App\Services\TimetableService;
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

class TimetableController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TimetableService $timetableService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.timetable', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.timetable', only: ['create', 'store']),
            new Middleware('can:edit.timetable', only: ['edit', 'update']),
            new Middleware('can:delete.timetable', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('timetables.index', $this->formOptions());
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->timetableService->query($request->only([
            'academic_year_id',
            'grade_id',
            'division_id',
            'timetable_type_id',
            'status',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if (! $keyword) {
                    return;
                }

                $query->where(function ($query) use ($keyword): void {
                    $query->where('code', 'like', "%{$keyword}%")
                        ->orWhere('timetable_name', 'like', "%{$keyword}%")
                        ->orWhere('status', 'like', "%{$keyword}%")
                        ->orWhereHas('academicYear', fn ($query) => $query->where('academic_year', 'like', "%{$keyword}%"))
                        ->orWhereHas('grade', fn ($query) => $query->where('grade', 'like', "%{$keyword}%"))
                        ->orWhereHas('divisions', fn ($query) => $query->where('division', 'like', "%{$keyword}%"))
                        ->orWhereHas('timetableType', fn ($query) => $query->where('title', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn (Timetable $timetable): string => sprintf(
                '<input type="checkbox" class="timetable-row-check" value="%d">',
                $timetable->id
            ))
            ->addColumn('academic_year', fn (Timetable $timetable): string => $timetable->academicYear?->academic_year ?? '-')
            ->addColumn('grade', fn (Timetable $timetable): string => $timetable->grade?->grade ?? '-')
            ->addColumn('division', fn (Timetable $timetable): string => $timetable->divisions->pluck('division')->implode(', ') ?: '-')
            ->editColumn('applicable_from', fn (Timetable $timetable): string => $timetable->applicable_from?->format('d M Y') ?? '-')
            ->editColumn('applicable_to', fn (Timetable $timetable): string => $timetable->applicable_to?->format('d M Y') ?? '-')
            ->editColumn('status', fn (Timetable $timetable): string => sprintf(
                '<span class="%s">%s</span>',
                $timetable->status === 'published' ? 'status-green' : 'status-red',
                ucfirst($timetable->status)
            ))
            ->addColumn('actions', fn (Timetable $timetable): string => $this->actionButtons($timetable))
            ->rawColumns(['select', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('timetables.form', [
            ...$this->formOptions(),
            'timetable' => new Timetable([
                'code' => $this->timetableService->nextCode(),
                'status' => 'draft',
            ]),
            'selectedDivisionIds' => collect(),
        ]);
    }

    public function store(TimetableRequest $request): RedirectResponse
    {
        $this->timetableService->create($request->validated(), $request->user());

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Regular timetable saved successfully.');
    }

    public function edit(Timetable $timetable): View
    {
        $timetable->load('divisions');

        return view('timetables.form', [
            ...$this->formOptions(),
            'timetable' => $timetable,
            'selectedDivisionIds' => $timetable->divisions->pluck('id'),
        ]);
    }

    public function update(TimetableRequest $request, Timetable $timetable): RedirectResponse
    {
        $this->timetableService->update($timetable, $request->validated());

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Regular timetable updated successfully.');
    }

    public function destroy(Request $request, Timetable $timetable): JsonResponse|RedirectResponse
    {
        $this->timetableService->delete($timetable);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Regular timetable deleted successfully.',
            ]);
        }

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Regular timetable deleted successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $timetables = $this->selectedTimetables($request);

        if ($timetables->isEmpty()) {
            return back()->with('error', 'Select at least one regular timetable to export.');
        }

        return Excel::download(new TimetablesExport($timetables), 'regular-timetables.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $timetables = $this->selectedTimetables($request);

        if ($timetables->isEmpty()) {
            return back()->with('error', 'Select at least one regular timetable to export.');
        }

        return Pdf::loadView('timetables.export-pdf', ['timetables' => $timetables])
            ->download('regular-timetables.pdf');
    }

    private function selectedTimetables(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->timetableService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'academicYears' => $this->timetableService->academicYears(),
            'grades' => $this->timetableService->grades(),
            'divisions' => $this->timetableService->divisions(),
            'timetableTypes' => $this->timetableService->timetableTypes(),
            'incharges' => $this->timetableService->incharges(),
            'statuses' => Timetable::STATUSES,
        ];
    }

    private function actionButtons(Timetable $timetable): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.timetable')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('timetables.edit', $timetable)
            );
        }

        if (request()->user()?->can('delete.timetable')) {
            $buttons .= view('timetables.partials.delete-button', compact('timetable'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
