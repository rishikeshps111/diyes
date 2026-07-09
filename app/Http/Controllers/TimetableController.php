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
            new Middleware('can:view.timetable', only: ['index', 'data', 'preview', 'downloadGeneratedPdf', 'exportExcel', 'exportPdf']),
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
                'prepared_by_id' => auth()->id(),
                'prepared_at' => now(),
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
        $timetable->load(['divisions', 'preparedBy']);

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
        $timetable->load(['academicYear', 'grade', 'divisions', 'timetableCategory', 'incharge', 'preparedBy', 'entries.subject', 'entries.teacherOne', 'entries.teacherTwo']);

        return view('timetables.generate', [
            'timetable' => $timetable,
            'entries' => $timetable->entries()
                ->with(['subject', 'teacherOne', 'teacherTwo'])
                ->get()
                ->sortBy(fn (TimetableEntry $entry): int => ((int) array_search($entry->day, TimetableEntry::DAYS, true) * 1000) + $entry->period_no)
                ->values(),
            'days' => TimetableEntry::DAYS,
            'entryTypes' => TimetableEntry::TYPES,
            'subjects' => Subject::query()->active()->where('grade_id', $timetable->grade_id)->orderBy('subject_name')->get(['id', 'subject_name', 'color']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeGenerated(TimetableGenerateRequest $request, Timetable $timetable): RedirectResponse
    {
        $validated = $request->validated();

        $periodEntries = collect($validated['entries'])
            ->map(function (array $entry) use ($timetable): array {
                $teacherIds = array_values($entry['teacher_ids'] ?? []);

                return [
                    'timetable_id' => $timetable->id,
                    'day' => $entry['day'],
                    'period_no' => $entry['period_no'],
                    'entry_type' => 'period',
                    'subject_id' => $entry['subject_id'],
                    'teacher_1_id' => $teacherIds[0] ?? null,
                    'teacher_2_id' => $teacherIds[1] ?? null,
                    'start_time' => $entry['start_time'],
                    'end_time' => $entry['end_time'],
                    'duration_minutes' => Carbon::createFromFormat('H:i', $entry['start_time'])
                        ->diffInMinutes(Carbon::createFromFormat('H:i', $entry['end_time'])),
                ];
            })
            ->values();

        $entriesByDayAndPeriod = $periodEntries->keyBy(fn (array $entry): string => $entry['day'].'|'.$entry['period_no']);
        $breakEntries = $periodEntries->pluck('day')->unique()->values()
            ->flatMap(function (string $day) use ($timetable, $validated, $entriesByDayAndPeriod): array {
                return collect([
                    [
                        'period_no' => (int) $validated['short_break_after_period'],
                        'entry_type' => 'short_break',
                        'duration_minutes' => (int) $timetable->short_break_minutes,
                    ],
                    [
                        'period_no' => (int) $validated['lunch_break_after_period'],
                        'entry_type' => 'lunch_break',
                        'duration_minutes' => (int) $timetable->lunch_break_minutes,
                    ],
                    [
                        'period_no' => (int) $validated['short_break_after_lunch_period'],
                        'entry_type' => 'short_break',
                        'duration_minutes' => (int) $timetable->short_break_after_lunch_minutes,
                    ],
                ])
                    ->map(function (array $break) use ($timetable, $day, $entriesByDayAndPeriod): ?array {
                        $periodEntry = $entriesByDayAndPeriod->get($day.'|'.$break['period_no']);

                        if (! $periodEntry) {
                            return null;
                        }

                        $startTime = $periodEntry['end_time'];
                        $endTime = Carbon::createFromFormat('H:i', $startTime)
                            ->addMinutes($break['duration_minutes'])
                            ->format('H:i');

                        return [
                            'timetable_id' => $timetable->id,
                            'day' => $day,
                            'period_no' => $break['period_no'],
                            'entry_type' => $break['entry_type'],
                            'subject_id' => null,
                            'teacher_1_id' => null,
                            'teacher_2_id' => null,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'duration_minutes' => $break['duration_minutes'],
                        ];
                    })
                    ->filter()
                    ->all();
            });

        $entries = $periodEntries->merge($breakEntries)->all();

        DB::transaction(function () use ($timetable, $entries): void {
            $timetable->entries()->delete();
            $timetable->entries()->createMany($entries);
        });

        return redirect()
            ->route('timetables.index')
            ->with('success', 'Generated timetable saved successfully.');
    }

    public function preview(Timetable $timetable): JsonResponse
    {
        return response()->json($this->previewData($timetable));
    }

    public function downloadGeneratedPdf(Timetable $timetable)
    {
        $data = $this->previewData($timetable);

        if ($data['periods']->isEmpty()) {
            return back()->with('error', 'No generated timetable entries found.');
        }

        $filename = str($timetable->timetable_name ?: 'generated-timetable')->slug().'-timetable.pdf';

        return Pdf::loadView('timetables.generated-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function previewData(Timetable $timetable): array
    {
        $timetable->loadMissing(['grade', 'divisions']);

        $entries = $timetable->entries()
            ->with(['subject', 'teacherOne', 'teacherTwo'])
            ->orderBy('period_no')
            ->get();

        return [
            'timetable' => [
                'name' => $timetable->timetable_name,
                'grade' => $timetable->grade?->grade ?? '-',
                'divisions' => $timetable->divisions->pluck('division')->implode(', ') ?: '-',
                'total_periods' => $timetable->total_periods_per_day,
                'short_break_minutes' => $timetable->short_break_minutes,
                'lunch_break_minutes' => $timetable->lunch_break_minutes,
                'short_break_after_lunch_minutes' => $timetable->short_break_after_lunch_minutes,
            ],
            'days' => $entries->pluck('day')->filter()->unique()->values(),
            'periods' => $entries
                ->where('entry_type', 'period')
                ->map(fn (TimetableEntry $entry): array => [
                    'day' => $entry->day,
                    'period_no' => $entry->period_no,
                    'subject' => $entry->subject?->subject_name ?? '-',
                    'color' => $entry->subject?->color ?? '#ffffff',
                    'teachers' => collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->values(),
                    'start_time' => substr((string) $entry->start_time, 0, 5),
                    'end_time' => substr((string) $entry->end_time, 0, 5),
                ])
                ->values(),
            'breaks' => $entries
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
        ];
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

        $isGenerated = (int) ($timetable->entries_count ?? 0) > 0;

        $menuItems = '';

        if ($isGenerated && request()->user()?->can('view.timetable')) {
            $menuItems .= sprintf(
                '<li><button type="button" class="dropdown-item timetable-preview-btn" data-preview-url="%s" data-pdf-url="%s">View TimeTable</button></li>',
                route('timetables.preview', $timetable),
                route('timetables.generated.pdf', $timetable)
            );
        }

        if (request()->user()?->can('edit.timetable')) {
            $menuItems .= sprintf(
                '<li><a class="dropdown-item" href="%s">%s</a></li>',
                route('timetables.generate', $timetable),
                $isGenerated ? 'Regenerate TimeTable' : 'Generate TimeTable'
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
}
