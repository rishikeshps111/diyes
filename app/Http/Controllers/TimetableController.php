<?php

namespace App\Http\Controllers;

use App\Exports\TimetablesExport;
use App\Http\Requests\TimetableGenerateRequest;
use App\Http\Requests\TimetableRequest;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Services\TimetableService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
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
            new Middleware('can:edit.timetable', only: ['edit', 'update', 'generate', 'storeGenerated']),
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
            'timetable_category_id',
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
                        ->orWhereHas('timetableCategory', fn ($query) => $query->where('title', 'like', "%{$keyword}%"));
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

    public function generate(Timetable $timetable): View
    {
        $timetable->load(['academicYear', 'grade', 'divisions', 'timetableCategory', 'incharge', 'entries.subject', 'entries.teacherOne', 'entries.teacherTwo']);

        return view('timetables.generate', [
            'timetable' => $timetable,
            'entries' => $timetable->entries()
                ->with(['subject', 'teacherOne', 'teacherTwo'])
                ->get()
                ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
                ->values(),
            'days' => TimetableEntry::DAYS,
            'entryTypes' => TimetableEntry::TYPES,
            'subjects' => Subject::query()->active()->where('grade_id', $timetable->grade_id)->orderBy('subject_name')->get(['id', 'subject_name']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeGenerated(TimetableGenerateRequest $request, Timetable $timetable): RedirectResponse
    {
        $entries = collect($request->validated('entries'))
            ->map(function (array $entry) use ($timetable): array {
                return [
                    'timetable_id' => $timetable->id,
                    'day' => $entry['day'],
                    'period_no' => $entry['period_no'],
                    'entry_type' => $entry['entry_type'],
                    'subject_id' => $entry['entry_type'] === 'period' ? ($entry['subject_id'] ?? null) : null,
                    'teacher_1_id' => $entry['entry_type'] === 'period' ? ($entry['teacher_1_id'] ?? null) : null,
                    'teacher_2_id' => $entry['entry_type'] === 'period' ? ($entry['teacher_2_id'] ?? null) : null,
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'duration_minutes' => Carbon::createFromFormat('H:i', $entry['start_time'])
                        ->diffInMinutes(Carbon::createFromFormat('H:i', $entry['end_time'])),
                ];
            })
            ->all();

        DB::transaction(function () use ($timetable, $entries): void {
            $timetable->entries()->delete();
            $timetable->entries()->createMany($entries);
        });

        return redirect()
            ->route('timetables.generate', $timetable)
            ->with('success', 'Generated timetable saved successfully.');
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
            'timetableCategories' => $this->timetableService->timetableCategories(),
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

        $menu = request()->user()?->can('edit.timetable')
            ? sprintf(
                '<div class="dropdown">
                    <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="%s">Generate TimeTable</a></li>
                    </ul>
                </div>',
                route('timetables.generate', $timetable)
            )
            : '';

        return '<div class="action-btns">'.$buttons.$menu.'</div>';
    }
}
