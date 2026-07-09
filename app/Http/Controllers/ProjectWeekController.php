<?php

namespace App\Http\Controllers;

use App\Exports\ProjectWeeksExport;
use App\Http\Requests\ProjectWeekGenerateRequest;
use App\Http\Requests\ProjectWeekRequest;
use App\Models\ProjectWeek;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Services\ProjectWeekService;
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

class ProjectWeekController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ProjectWeekService $projectWeekService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.project-week', only: ['index', 'data', 'preview', 'downloadGeneratedPdf', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.project-week', only: ['create', 'store']),
            new Middleware('can:edit.project-week', only: ['edit', 'update', 'generate', 'storeGenerated']),
            new Middleware('can:delete.project-week', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('project-weeks.index', $this->formOptions());
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->projectWeekService->query($request->only([
            'grade_id',
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
                        ->orWhere('status', 'like', "%{$keyword}%")
                        ->orWhereHas('project', function ($query) use ($keyword): void {
                            $query->where('project_title', 'like', "%{$keyword}%")
                                ->orWhere('project_code', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('academicYear', fn ($query) => $query->where('academic_year', 'like', "%{$keyword}%"))
                        ->orWhereHas('grade', fn ($query) => $query->where('grade', 'like', "%{$keyword}%"))
                        ->orWhereHas('divisions', fn ($query) => $query->where('division', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn (ProjectWeek $projectWeek): string => sprintf(
                '<input type="checkbox" class="project-week-row-check" value="%d">',
                $projectWeek->id
            ))
            ->addColumn('project', fn (ProjectWeek $projectWeek): string => $projectWeek->project?->project_title ?? '-')
            ->addColumn('academic_year', fn (ProjectWeek $projectWeek): string => $projectWeek->academicYear?->academic_year ?? '-')
            ->addColumn('grade', fn (ProjectWeek $projectWeek): string => $projectWeek->grade?->grade ?? '-')
            ->addColumn('division', fn (ProjectWeek $projectWeek): string => $projectWeek->divisions->pluck('division')->implode(', ') ?: '-')
            ->editColumn('applicable_from', fn (ProjectWeek $projectWeek): string => $projectWeek->applicable_from?->format('d M Y') ?? '-')
            ->editColumn('applicable_to', fn (ProjectWeek $projectWeek): string => $projectWeek->applicable_to?->format('d M Y') ?? '-')
            ->editColumn('status', fn (ProjectWeek $projectWeek): string => sprintf(
                '<span class="%s">%s</span>',
                $projectWeek->status === 'publish' ? 'status-green' : 'status-red',
                ProjectWeek::STATUSES[$projectWeek->status] ?? ucfirst($projectWeek->status)
            ))
            ->addColumn('actions', fn (ProjectWeek $projectWeek): string => $this->actionButtons($projectWeek))
            ->rawColumns(['select', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('project-weeks.form', [
            ...$this->formOptions(),
            'projectWeek' => new ProjectWeek([
                'code' => $this->projectWeekService->nextCode(),
                'status' => 'draft',
            ]),
            'selectedDivisionIds' => collect(),
        ]);
    }

    public function store(ProjectWeekRequest $request): RedirectResponse
    {
        $this->projectWeekService->create($request->validated());

        return redirect()
            ->route('project-weeks.index')
            ->with('success', 'Project week saved successfully.');
    }

    public function edit(ProjectWeek $projectWeek): View
    {
        $projectWeek->load(['divisions', 'creator']);

        return view('project-weeks.form', [
            ...$this->formOptions(),
            'projectWeek' => $projectWeek,
            'selectedDivisionIds' => $projectWeek->divisions->pluck('id'),
        ]);
    }

    public function update(ProjectWeekRequest $request, ProjectWeek $projectWeek): RedirectResponse
    {
        $this->projectWeekService->update($projectWeek, $request->validated());

        return redirect()
            ->route('project-weeks.index')
            ->with('success', 'Project week updated successfully.');
    }

    public function destroy(Request $request, ProjectWeek $projectWeek): JsonResponse|RedirectResponse
    {
        $this->projectWeekService->delete($projectWeek);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Project week deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-weeks.index')
            ->with('success', 'Project week deleted successfully.');
    }

    public function generate(ProjectWeek $projectWeek): View|RedirectResponse
    {
        $projectWeek->load(['project', 'academicYear', 'grade', 'divisions', 'creator', 'entries.teacherOne', 'entries.teacherTwo']);

        $timetable = $this->matchingTimetable($projectWeek);

        if (! $timetable) {
            return redirect()
                ->route('project-weeks.index')
                ->with('error', 'No generated regular timetable found for this project week academic year, grade and divisions.');
        }

        $timetable->load(['entries.subject', 'entries.teacherOne', 'entries.teacherTwo']);

        return view('project-weeks.generate', [
            'projectWeek' => $projectWeek,
            'timetable' => $timetable,
            'entries' => $timetable->entries
                ->whereIn('entry_type', ['period', 'short_break', 'lunch_break'])
                ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
                ->values(),
            'savedProjectEntries' => $projectWeek->entries->keyBy('timetable_entry_id'),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeGenerated(ProjectWeekGenerateRequest $request, ProjectWeek $projectWeek): RedirectResponse
    {
        $projectWeek->load('divisions');
        $timetable = $this->matchingTimetable($projectWeek);

        if (! $timetable) {
            return redirect()
                ->route('project-weeks.index')
                ->with('error', 'No generated regular timetable found for this project week academic year, grade and divisions.');
        }

        $validated = $request->validated();
        $sourceEntries = $timetable->entries()
            ->where('entry_type', 'period')
            ->whereKey(collect($validated['entries'])->pluck('timetable_entry_id')->all())
            ->get()
            ->keyBy('id');

        $entries = collect($validated['entries'])
            ->map(function (array $entry) use ($sourceEntries): ?array {
                $sourceEntry = $sourceEntries->get((int) $entry['timetable_entry_id']);

                if (! $sourceEntry) {
                    return null;
                }

                $teacherIds = array_values($entry['teacher_ids'] ?? []);

                return [
                    'timetable_entry_id' => $sourceEntry->id,
                    'day' => $sourceEntry->day,
                    'period_no' => $sourceEntry->period_no,
                    'teacher_1_id' => $teacherIds[0] ?? null,
                    'teacher_2_id' => $teacherIds[1] ?? null,
                ];
            })
            ->filter()
            ->values();

        $tooManyForDay = $entries
            ->groupBy('day')
            ->first(fn ($dayEntries) => $dayEntries->count() > (int) $projectWeek->total_periods);

        if ($tooManyForDay) {
            return back()
                ->withInput()
                ->with('error', 'You can select only '.$projectWeek->total_periods.' project period(s) for each day.');
        }

        DB::transaction(function () use ($projectWeek, $timetable, $entries): void {
            $projectWeek->forceFill(['source_timetable_id' => $timetable->id])->save();
            $projectWeek->entries()->delete();
            $projectWeek->entries()->createMany($entries->all());
        });

        return redirect()
            ->route('project-weeks.index')
            ->with('success', 'Project week timetable generated successfully.');
    }

    public function preview(ProjectWeek $projectWeek): JsonResponse
    {
        return response()->json($this->previewData($projectWeek));
    }

    public function downloadGeneratedPdf(ProjectWeek $projectWeek)
    {
        $data = $this->previewData($projectWeek);

        if ($data['projectPeriods']->isEmpty()) {
            return back()->with('error', 'No generated project week timetable entries found.');
        }

        $filename = str($projectWeek->code.'-'.$projectWeek->project?->project_title)->slug().'-project-week.pdf';

        return Pdf::loadView('project-weeks.generated-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $projectWeeks = $this->selectedProjectWeeks($request);

        if ($projectWeeks->isEmpty()) {
            return back()->with('error', 'Select at least one project week to export.');
        }

        return Excel::download(new ProjectWeeksExport($projectWeeks), 'project-weeks.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $projectWeeks = $this->selectedProjectWeeks($request);

        if ($projectWeeks->isEmpty()) {
            return back()->with('error', 'Select at least one project week to export.');
        }

        return Pdf::loadView('project-weeks.export-pdf', ['projectWeeks' => $projectWeeks])
            ->download('project-weeks.pdf');
    }

    private function selectedProjectWeeks(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->projectWeekService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'projects' => $this->projectWeekService->projects(),
            'academicYears' => $this->projectWeekService->academicYears(),
            'grades' => $this->projectWeekService->grades(),
            'divisions' => $this->projectWeekService->divisions(),
            'statuses' => ProjectWeek::STATUSES,
        ];
    }

    private function actionButtons(ProjectWeek $projectWeek): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.project-week')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('project-weeks.edit', $projectWeek)
            );
        }

        if (request()->user()?->can('delete.project-week')) {
            $buttons .= view('project-weeks.partials.delete-button', compact('projectWeek'))->render();
        }

        $hasGenerated = (int) ($projectWeek->entries_count ?? 0) > 0;
        $menuItems = '';

        if ($hasGenerated && request()->user()?->can('view.project-week')) {
            $menuItems .= sprintf(
                '<li><button type="button" class="dropdown-item project-week-preview-btn" data-preview-url="%s" data-pdf-url="%s">View TimeTable</button></li>',
                route('project-weeks.preview', $projectWeek),
                route('project-weeks.generated.pdf', $projectWeek)
            );
        }

        if (request()->user()?->can('edit.project-week')) {
            $menuItems .= sprintf(
                '<li><a class="dropdown-item" href="%s">%s</a></li>',
                route('project-weeks.generate', $projectWeek),
                $hasGenerated ? 'Regenerate TimeTable' : 'Generate TimeTable'
            );
        }

        $menu = $menuItems
            ? sprintf(
                '<div class="dropdown">
                    <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        %s
                    </ul>
                </div>',
                $menuItems
            )
            : '';

        return '<div class="action-btns">'.$buttons.$menu.'</div>';
    }

    private function matchingTimetable(ProjectWeek $projectWeek): ?Timetable
    {
        $divisionIds = $projectWeek->divisions->pluck('id')->all();

        return Timetable::query()
            ->with(['entries.subject', 'entries.teacherOne', 'entries.teacherTwo', 'divisions'])
            ->withCount('entries')
            ->where('academic_year_id', $projectWeek->academic_year_id)
            ->where('grade_id', $projectWeek->grade_id)
            ->where('status', 'published')
            ->where('applicable_from', '<=', $projectWeek->applicable_from)
            ->where('applicable_to', '>=', $projectWeek->applicable_to)
            ->whereHas('entries')
            ->when($divisionIds, function ($query) use ($divisionIds): void {
                foreach ($divisionIds as $divisionId) {
                    $query->whereHas('divisions', fn ($divisionQuery) => $divisionQuery->whereKey($divisionId));
                }
            })
            ->latest('created_at')
            ->first();
    }

    private function previewData(ProjectWeek $projectWeek): array
    {
        $projectWeek->loadMissing([
            'project',
            'grade',
            'divisions',
            'sourceTimetable.entries.subject',
            'sourceTimetable.entries.teacherOne',
            'sourceTimetable.entries.teacherTwo',
            'entries.teacherOne',
            'entries.teacherTwo',
        ]);

        $timetable = $projectWeek->sourceTimetable ?: $this->matchingTimetable($projectWeek);
        $sourceEntries = $timetable
            ? $timetable->entries->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)->values()
            : collect();
        $projectEntries = $projectWeek->entries->keyBy('timetable_entry_id');

        return [
            'projectWeek' => [
                'code' => $projectWeek->code,
                'project' => $projectWeek->project?->project_title ?? '-',
                'grade' => $projectWeek->grade?->grade ?? '-',
                'divisions' => $projectWeek->divisions->pluck('division')->implode(', ') ?: '-',
                'applicable_from' => $projectWeek->applicable_from?->format('d M Y') ?? '-',
                'applicable_to' => $projectWeek->applicable_to?->format('d M Y') ?? '-',
                'total_periods' => $timetable?->total_periods_per_day ?? 0,
            ],
            'days' => $sourceEntries->pluck('day')->filter()->unique()->values(),
            'periods' => $sourceEntries
                ->where('entry_type', 'period')
                ->map(function (TimetableEntry $entry) use ($projectEntries, $projectWeek): array {
                    $projectEntry = $projectEntries->get($entry->id);

                    return [
                        'day' => $entry->day,
                        'period_no' => $entry->period_no,
                        'subject' => $projectEntry ? ($projectWeek->project?->project_title ?? 'Project Period') : ($entry->subject?->subject_name ?? '-'),
                        'color' => $projectEntry ? '#dff7df' : ($entry->subject?->color ?? '#ffffff'),
                        'teachers' => $projectEntry
                            ? collect([$projectEntry->teacherOne?->name, $projectEntry->teacherTwo?->name])->filter()->values()
                            : collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->values(),
                        'start_time' => substr((string) $entry->start_time, 0, 5),
                        'end_time' => substr((string) $entry->end_time, 0, 5),
                        'is_project_period' => (bool) $projectEntry,
                    ];
                })
                ->values(),
            'breaks' => $sourceEntries
                ->whereIn('entry_type', ['short_break', 'lunch_break'])
                ->map(fn (TimetableEntry $entry): array => [
                    'day' => $entry->day,
                    'period_no' => $entry->period_no,
                    'type' => $entry->entry_type,
                    'label' => $entry->entry_type === 'lunch_break' ? 'Lunch Break' : 'Break',
                    'duration_minutes' => $entry->duration_minutes,
                    'start_time' => substr((string) $entry->start_time, 0, 5),
                    'end_time' => substr((string) $entry->end_time, 0, 5),
                ])
                ->values(),
            'projectPeriods' => $projectWeek->entries,
        ];
    }
}
