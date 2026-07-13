<?php

namespace App\Http\Controllers;

use App\Exports\SpecialEventsExport;
use App\Http\Requests\SpecialEventGenerateRequest;
use App\Http\Requests\SpecialEventRequest;
use App\Mail\SpecialEventDetailsMail;
use App\Models\Division;
use App\Models\ProjectWeek;
use App\Models\SpecialEvent;
use App\Models\SpecialEventTimetableEntry;
use App\Models\TimetableEntry;
use App\Services\SpecialEventService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class SpecialEventController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SpecialEventService $specialEventService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.special-event', only: ['index', 'data', 'show', 'divisionsByGrades', 'preview', 'downloadGeneratedPdf', 'sendMail', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.special-event', only: ['create', 'store']),
            new Middleware('can:edit.special-event', only: ['edit', 'update', 'generate', 'storeGenerated']),
            new Middleware('can:delete.special-event', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('special-events.index', [
            'eventTypes' => $this->specialEventService->eventTypes(),
            'statuses' => SpecialEvent::STATUSES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->specialEventService->query($request->only([
            'event_type_id',
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
                    $query->where('event_code', 'like', "%{$keyword}%")
                        ->orWhere('event_title', 'like', "%{$keyword}%")
                        ->orWhere('venue', 'like', "%{$keyword}%")
                        ->orWhereHas('eventType', fn ($eventTypeQuery) => $eventTypeQuery->where('title', 'like', "%{$keyword}%"))
                        ->orWhereHas('staffCoordinators', fn ($staffQuery) => $staffQuery->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('teacherCoordinators', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('grades', fn ($gradeQuery) => $gradeQuery->where('grade', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn (SpecialEvent $specialEvent): string => sprintf(
                '<input type="checkbox" class="special-event-row-check" value="%d">',
                $specialEvent->id
            ))
            ->editColumn('event_start_date', fn (SpecialEvent $specialEvent): string => $specialEvent->event_start_date?->format('d M Y') ?? '-')
            ->editColumn('event_end_date', fn (SpecialEvent $specialEvent): string => $specialEvent->event_end_date?->format('d M Y') ?? '-')
            ->addColumn('coordinator', fn (SpecialEvent $specialEvent): string => $this->badges(
                collect()
                    ->merge($specialEvent->staffCoordinators->pluck('name'))
                    ->merge($specialEvent->teacherCoordinators->pluck('name'))
                    ->all()
            ))
            ->addColumn('applicable_classes', fn (SpecialEvent $specialEvent): string => $this->badges($specialEvent->grades->pluck('grade')->all()))
            ->editColumn('status', fn (SpecialEvent $specialEvent): string => sprintf(
                '<span class="%s">%s</span>',
                $this->statusClass($specialEvent->status),
                SpecialEvent::STATUSES[$specialEvent->status] ?? ucfirst($specialEvent->status)
            ))
            ->addColumn('actions', fn (SpecialEvent $specialEvent): string => $this->actionButtons($specialEvent))
            ->rawColumns(['select', 'coordinator', 'applicable_classes', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('special-events.form', [
            'specialEvent' => new SpecialEvent([
                'event_code' => $this->specialEventService->nextCode(),
                'media_coverable' => false,
                'outside_candidates' => false,
                'status' => 'draft',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(SpecialEventRequest $request): RedirectResponse
    {
        $this->specialEventService->create($request->validated());

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event created successfully.');
    }

    public function show(SpecialEvent $specialEvent): View
    {
        $specialEvent->load([
            'eventType',
            'academicYear',
            'grades',
            'divisions',
            'staffCoordinators',
            'teacherCoordinators',
            'timings',
            'attachments',
            'creator',
        ]);

        return view('special-events.show', compact('specialEvent'));
    }

    public function edit(SpecialEvent $specialEvent): View
    {
        $specialEvent->load([
            'grades',
            'divisions',
            'staffCoordinators',
            'teacherCoordinators',
            'timings',
            'attachments',
        ]);

        return view('special-events.form', [
            'specialEvent' => $specialEvent,
            ...$this->formOptions(),
        ]);
    }

    public function update(SpecialEventRequest $request, SpecialEvent $specialEvent): RedirectResponse
    {
        $this->specialEventService->update($specialEvent, $request->validated());

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event updated successfully.');
    }

    public function destroy(Request $request, SpecialEvent $specialEvent): JsonResponse|RedirectResponse
    {
        $this->specialEventService->delete($specialEvent);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Special event deleted successfully.']);
        }

        return redirect()
            ->route('special-events.index')
            ->with('success', 'Special event deleted successfully.');
    }

    public function divisionsByGrades(Request $request): JsonResponse
    {
        $gradeIds = collect($request->input('grade_ids', []))->filter()->map(fn ($id): int => (int) $id)->all();

        return response()->json(
            Division::query()
                ->active()
                ->with('grade')
                ->when($gradeIds, fn ($query) => $query->whereIn('grade_id', $gradeIds))
                ->orderBy('division')
                ->get(['id', 'division', 'grade_id'])
                ->map(fn (Division $division): array => [
                    'id' => $division->id,
                    'text' => ($division->grade?->grade ? $division->grade->grade.' - ' : '').$division->division,
                    'grade_id' => $division->grade_id,
                ])
        );
    }

    public function generate(Request $request, SpecialEvent $specialEvent): View|RedirectResponse
    {
        $specialEvent->load(['eventType', 'academicYear', 'grades', 'divisions.grade', 'timetableEntries']);
        $gradeId = (int) ($request->input('grade_id') ?: $specialEvent->grades->first()?->id);
        $divisionId = (int) ($request->input('division_id') ?: $specialEvent->divisions->firstWhere('grade_id', $gradeId)?->id);
        $projectWeek = $this->matchingProjectWeek($specialEvent, $gradeId, $divisionId);

        if (! $projectWeek) {
            return redirect()->route('special-events.index')
                ->with('error', 'No generated Project Week timetable found for this event, grade and division.');
        }

        $sourceEntries = $this->projectWeekSourceEntries($projectWeek);

        return view('special-events.generate', [
            'specialEvent' => $specialEvent,
            'projectWeek' => $projectWeek,
            'entries' => $sourceEntries,
            'grades' => $specialEvent->grades,
            'divisions' => $specialEvent->divisions,
            'selectedGradeId' => $gradeId,
            'selectedDivisionId' => $divisionId,
            'generatedGradeIds' => $specialEvent->timetableEntries->pluck('grade_id')->unique(),
            'generatedDivisionIds' => $specialEvent->timetableEntries->pluck('division_id')->unique(),
            'savedEventPeriods' => $specialEvent->timetableEntries->where('is_event_period', true)
                ->map(fn (SpecialEventTimetableEntry $entry): string => $entry->day.'|'.$entry->period_no)->unique(),
        ]);
    }

    public function storeGenerated(SpecialEventGenerateRequest $request, SpecialEvent $specialEvent): RedirectResponse
    {
        $validated = $request->validated();
        $selected = collect($validated['entries'])->map(fn (array $entry): string => $entry['day'].'|'.$entry['period_no']);
        $rows = collect();

        foreach ($validated['grade_ids'] as $gradeId) {
            $divisionIds = Division::query()->whereIn('id', $validated['division_ids'])->where('grade_id', $gradeId)->pluck('id');
            foreach ($divisionIds as $divisionId) {
                $projectWeek = $this->matchingProjectWeek($specialEvent, (int) $gradeId, (int) $divisionId);
                if (! $projectWeek) {
                    return back()->withInput()->with('error', 'A generated Project Week timetable is required for every selected grade and division.');
                }

                $rows = $rows->merge($this->projectWeekSourceEntries($projectWeek)->map(function (array $entry) use ($specialEvent, $gradeId, $divisionId, $projectWeek, $selected): array {
                    $isEvent = $entry['entry_type'] === 'period' && $selected->contains($entry['day'].'|'.$entry['period_no']);
                    return [
                        'special_event_id' => $specialEvent->id, 'grade_id' => $gradeId, 'division_id' => $divisionId,
                        'project_week_id' => $projectWeek->id, 'timetable_entry_id' => $entry['timetable_entry_id'],
                        'day' => $entry['day'], 'period_no' => $entry['period_no'], 'entry_type' => $entry['entry_type'],
                        'subject_name' => $isEvent ? $specialEvent->event_title : $entry['subject_name'],
                        'teacher_names' => $entry['teacher_names'], 'start_time' => $entry['start_time'], 'end_time' => $entry['end_time'],
                        'duration_minutes' => $entry['duration_minutes'], 'is_event_period' => $isEvent,
                    ];
                }));
            }
        }

        DB::transaction(function () use ($specialEvent, $validated, $rows): void {
            $specialEvent->timetableEntries()->whereIn('grade_id', $validated['grade_ids'])->whereIn('division_id', $validated['division_ids'])->delete();
            $specialEvent->timetableEntries()->createMany($rows->all());
        });

        return redirect()->route('special-events.index')->with('success', 'Special event timetable generated successfully.');
    }

    public function preview(SpecialEvent $specialEvent): JsonResponse
    {
        return response()->json($this->generatedTimetableData($specialEvent));
    }

    public function downloadGeneratedPdf(SpecialEvent $specialEvent)
    {
        $data = $this->generatedTimetableData($specialEvent);
        if ($data['timetables']->isEmpty()) return back()->with('error', 'No generated special event timetable found.');

        return Pdf::loadView('special-events.generated-pdf', $data)->setPaper('a4', 'landscape')
            ->download(str($specialEvent->event_code.'-'.$specialEvent->event_title)->slug().'-timetable.pdf');
    }

    public function sendMail(Request $request, SpecialEvent $specialEvent): JsonResponse
    {
        $emails = collect(preg_split('/[\s,;]+/', (string) $request->input('emails')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validator = Validator::make([
            ...$request->only(['subject', 'description']),
            'emails' => $emails,
        ], [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'emails' => ['required', 'array', 'min:1', 'max:10'],
            'emails.*' => ['email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check the mail details.',
                'errors' => $validator->errors(),
            ], 422);
        }

        Mail::to($emails)->queue(new SpecialEventDetailsMail(
            $specialEvent,
            $request->input('subject'),
            $request->input('description'),
        ));

        return response()->json([
            'message' => 'Special event mail queued successfully.',
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $specialEvents = $this->selectedSpecialEvents($request);

        if ($specialEvents->isEmpty()) {
            return back()->with('error', 'Select at least one special event to export.');
        }

        return Excel::download(new SpecialEventsExport($specialEvents), 'special-events.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $specialEvents = $this->selectedSpecialEvents($request);

        if ($specialEvents->isEmpty()) {
            return back()->with('error', 'Select at least one special event to export.');
        }

        return Pdf::loadView('special-events.export-pdf', ['specialEvents' => $specialEvents])
            ->download('special-events.pdf');
    }

    private function formOptions(): array
    {
        return [
            'eventTypes' => $this->specialEventService->eventTypes(),
            'academicYears' => $this->specialEventService->academicYears(),
            'grades' => $this->specialEventService->grades(),
            'divisions' => $this->specialEventService->divisions(),
            'staff' => $this->specialEventService->staff(),
            'teachers' => $this->specialEventService->teachers(),
            'participants' => SpecialEvent::PARTICIPANTS,
            'statuses' => SpecialEvent::STATUSES,
        ];
    }

    private function selectedSpecialEvents(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->specialEventService->selectedForExport($ids);
    }

    private function actionButtons(SpecialEvent $specialEvent): string
    {
        $buttons = sprintf(
            '<a href="%s" class="btn-edit" title="View"><i class="fa-solid fa-eye"></i></a>',
            route('special-events.show', $specialEvent)
        );

        $buttons .= sprintf(
            '<a href="#!" class="btn-view special-event-mail-btn" title="Send Mail" data-mail-url="%s" data-event-title="%s"><i class="fa-solid fa-user"></i></a>',
            route('special-events.send-mail', $specialEvent),
            e($specialEvent->event_title)
        );

        if (request()->user()?->can('edit.special-event')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('special-events.edit', $specialEvent)
            );
        }

        if (request()->user()?->can('delete.special-event')) {
            $buttons .= view('special-events.partials.delete-button', compact('specialEvent'))->render();
        }

        $hasGenerated = (int) ($specialEvent->timetable_entries_count ?? 0) > 0;
        $items = '';
        if ($hasGenerated && request()->user()?->can('view.special-event')) {
            $items .= sprintf('<li><button type="button" class="dropdown-item special-event-preview-btn" data-preview-url="%s" data-pdf-url="%s">View TimeTable</button></li>', route('special-events.preview', $specialEvent), route('special-events.generated.pdf', $specialEvent));
        }
        if (request()->user()?->can('edit.special-event')) {
            $items .= sprintf('<li><a class="dropdown-item" href="%s">%s</a></li>', route('special-events.generate', $specialEvent), $hasGenerated ? 'Regenerate TimeTable' : 'Generate TimeTable');
        }
        if ($items) $buttons .= '<div class="dropdown"><button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button><ul class="dropdown-menu dropdown-menu-end">'.$items.'</ul></div>';

        return '<div class="action-btns">'.$buttons.'</div>';
    }

    private function badges(array $items): string
    {
        if (empty($items)) {
            return '-';
        }

        return collect($items)
            ->filter()
            ->map(fn (string $item): string => '<span class="badge bg-light text-dark border me-1 mb-1">'.e($item).'</span>')
            ->implode('');
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'active', 'complete' => 'status-green',
            'cancelled', 'inactive' => 'status-red',
            'draft', 'postponed' => 'status-orange',
            default => '',
        };
    }

    private function matchingProjectWeek(SpecialEvent $event, int $gradeId, int $divisionId): ?ProjectWeek
    {
        return ProjectWeek::query()->with(['project', 'grade', 'divisions', 'sourceTimetable.entries.subject', 'sourceTimetable.entries.teacherOne', 'sourceTimetable.entries.teacherTwo', 'entries.teacherOne', 'entries.teacherTwo'])
            ->where('academic_year_id', $event->academic_year_id)->where('grade_id', $gradeId)->where('status', 'publish')
            ->where('applicable_from', '<=', $event->event_start_date)->where('applicable_to', '>=', $event->event_end_date)
            ->whereHas('divisions', fn ($query) => $query->whereKey($divisionId))->whereHas('entries')->latest()->first();
    }

    private function projectWeekSourceEntries(ProjectWeek $projectWeek)
    {
        $projectEntries = $projectWeek->entries->keyBy('timetable_entry_id');
        return collect($projectWeek->sourceTimetable?->entries)->whereIn('entry_type', ['period', 'short_break', 'lunch_break'])
            ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
            ->map(function (TimetableEntry $entry) use ($projectWeek, $projectEntries): array {
                $override = $projectEntries->get($entry->id);
                return ['timetable_entry_id' => $entry->id, 'day' => $entry->day, 'period_no' => $entry->period_no, 'entry_type' => $entry->entry_type,
                    'subject_name' => $override ? ($projectWeek->project?->project_title ?? 'Project Period') : $entry->subject?->subject_name,
                    'color' => $override ? '#dff7df' : ($entry->subject?->color ?? '#ffffff'),
                    'teacher_names' => $override ? collect([$override->teacherOne?->name, $override->teacherTwo?->name])->filter()->values()->all() : collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->values()->all(),
                    'start_time' => substr((string) $entry->start_time, 0, 5), 'end_time' => substr((string) $entry->end_time, 0, 5), 'duration_minutes' => $entry->duration_minutes, 'is_project_period' => (bool) $override];
            })->values();
    }

    private function generatedTimetableData(SpecialEvent $specialEvent): array
    {
        $specialEvent->loadMissing(['timetableEntries.grade', 'timetableEntries.division', 'timetableEntries.timetableEntry.subject', 'timetableEntries.projectWeek.entries']);
        $groups = $specialEvent->timetableEntries->groupBy(fn ($entry) => $entry->grade_id.'-'.$entry->division_id)->map(function ($entries) {
            $first = $entries->first();
            return ['grade' => $first->grade?->grade ?? '-', 'division' => $first->division?->division ?? '-', 'days' => $entries->pluck('day')->unique()->values(), 'total_periods' => $entries->where('entry_type', 'period')->max('period_no') ?? 0,
                'periods' => $entries->where('entry_type', 'period')->map(function ($entry): array {
                    $isProject = $entry->projectWeek?->entries->contains('timetable_entry_id', $entry->timetable_entry_id) ?? false;
                    return ['day' => $entry->day, 'period_no' => $entry->period_no, 'subject' => $entry->subject_name, 'teachers' => $entry->teacher_names ?? [], 'start_time' => substr((string) $entry->start_time, 0, 5), 'end_time' => substr((string) $entry->end_time, 0, 5), 'is_event_period' => $entry->is_event_period, 'is_project_period' => $isProject, 'color' => $entry->is_event_period ? '#dbeafe' : ($isProject ? '#dff7df' : ($entry->timetableEntry?->subject?->color ?? '#ffffff'))];
                })->values(),
                'breaks' => $entries->whereIn('entry_type', ['short_break', 'lunch_break'])->unique(fn ($entry) => $entry->entry_type.'-'.$entry->period_no)->map(fn ($entry) => ['period_no' => $entry->period_no, 'type' => $entry->entry_type, 'duration_minutes' => $entry->duration_minutes, 'start_time' => substr((string) $entry->start_time, 0, 5), 'end_time' => substr((string) $entry->end_time, 0, 5)])->values()];
        })->values();
        return ['specialEvent' => ['title' => $specialEvent->event_title, 'from' => $specialEvent->event_start_date?->format('d M Y'), 'to' => $specialEvent->event_end_date?->format('d M Y')], 'timetables' => $groups];
    }
}
