<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\ProjectWeek;
use App\Models\SpecialEventTimetableEntry;
use App\Models\SubstituteAllocation;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeneratedTimetableController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);

        return view('generated-timetable.index', array_merge($this->options(), [
            'filters' => $filters,
            'preview' => $filters['academic_year_id'] && $filters['grade_id'] && $filters['division_id']
                ? $this->buildPreview($filters)
                : null,
        ]));
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        abort_unless($filters['academic_year_id'] && $filters['grade_id'] && $filters['division_id'], 422);
        $preview = $this->buildPreview($filters);

        return Pdf::loadView('generated-timetable.pdf', compact('preview'))
            ->setPaper('a4', 'landscape')
            ->download('weekly-timetable-'.$preview['week_start']->format('Y-m-d').'.pdf');
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'grade_id' => ['nullable', 'integer', 'exists:grades,id'],
            'division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'types' => ['nullable', 'array'],
            'types.*' => ['in:regular,special,project,substitute'],
            'types_present' => ['nullable', 'boolean'],
        ]);

        return [
            'academic_year_id' => $validated['academic_year_id'] ?? null,
            'grade_id' => $validated['grade_id'] ?? null,
            'division_id' => $validated['division_id'] ?? null,
            'types' => array_values($validated['types'] ?? (isset($validated['types_present']) ? [] : ['regular', 'special', 'project', 'substitute'])),
        ];
    }

    private function options(): array
    {
        return [
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'grades' => Grade::query()->active()->orderBy('grade')->get(),
            'divisions' => Division::query()->active()->orderBy('division')->get(),
        ];
    }

    public function buildPreview(array $filters): array
    {
        $weekStart = isset($filters['week_start']) ? Carbon::parse($filters['week_start'])->startOfDay() : now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = isset($filters['week_end']) ? Carbon::parse($filters['week_end'])->endOfDay() : $weekStart->copy()->addDays(6)->endOfDay();
        $types = collect($filters['types']);
        $days = collect(TimetableEntry::DAYS)->mapWithKeys(fn ($day, $index) => [$day => $weekStart->copy()->addDays($index)]);

        $timetable = Timetable::query()
            ->with(['entries.subject', 'entries.teacherOne', 'entries.teacherTwo', 'grade', 'divisions'])
            ->where('academic_year_id', $filters['academic_year_id'])
            ->where('grade_id', $filters['grade_id'])
            ->whereHas('divisions', fn ($query) => $query->whereKey($filters['division_id']))
            ->whereDate('applicable_from', '<=', $weekEnd)
            ->whereDate('applicable_to', '>=', $weekStart)
            ->latest('applicable_from')->first();

        $cells = collect();
        if ($timetable && $types->contains('regular')) {
            foreach ($timetable->entries->where('entry_type', 'period') as $entry) {
                $cells->put($entry->day.'|'.$entry->period_no, $this->regularCell($entry));
            }
        }

        if ($timetable && $types->contains('project')) {
            $projectWeeks = ProjectWeek::query()->with(['project', 'entries.teacherOne', 'entries.teacherTwo'])
                ->where('academic_year_id', $filters['academic_year_id'])->where('grade_id', $filters['grade_id'])
                ->whereHas('divisions', fn ($query) => $query->whereKey($filters['division_id']))
                ->whereDate('applicable_from', '<=', $weekEnd)->whereDate('applicable_to', '>=', $weekStart)->get();
            foreach ($projectWeeks as $projectWeek) {
                foreach ($projectWeek->entries as $entry) {
                    $source = $timetable->entries->firstWhere('id', $entry->timetable_entry_id);
                    if (! $source || ! $this->dateIsWithin($days->get($entry->day), $projectWeek->applicable_from, $projectWeek->applicable_to)) continue;
                    $cells->put($entry->day.'|'.$entry->period_no, [
                        'title' => $projectWeek->project?->project_title ?? 'Project Week', 'teachers' => collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->implode(', '),
                        'teacher_ids' => collect([$entry->teacher_1_id, $entry->teacher_2_id])->filter()->map(fn ($id) => (int) $id)->values()->all(),
                        'time' => $this->time($source), 'color' => '#dcfce7', 'type' => 'project', 'label' => 'Project Week',
                    ]);
                }
            }
        }

        if ($types->contains('special')) {
            $eventEntries = SpecialEventTimetableEntry::query()->with('specialEvent')
                ->where('grade_id', $filters['grade_id'])->where('division_id', $filters['division_id'])->where('is_event_period', true)
                ->whereHas('specialEvent', fn ($query) => $query->where('academic_year_id', $filters['academic_year_id'])->whereDate('event_start_date', '<=', $weekEnd)->whereDate('event_end_date', '>=', $weekStart))
                ->get();
            foreach ($eventEntries as $entry) {
                if (! $this->dateIsWithin($days->get($entry->day), $entry->specialEvent->event_start_date, $entry->specialEvent->event_end_date)) continue;
                $cells->put($entry->day.'|'.$entry->period_no, [
                    'title' => $entry->specialEvent->event_title, 'teachers' => collect($entry->teacher_names)->filter()->implode(', '),
                    'teacher_ids' => [], 'teacher_names' => collect($entry->teacher_names)->filter()->values()->all(),
                    'time' => substr((string) $entry->start_time, 0, 5).' - '.substr((string) $entry->end_time, 0, 5), 'color' => '#dbeafe', 'type' => 'special', 'label' => 'Special Event',
                ]);
            }
        }

        if ($types->contains('substitute')) {
            $allocations = SubstituteAllocation::query()->with(['subject', 'teacher', 'substituteTeacher', 'timetableEntry'])
                ->where('grade_id', $filters['grade_id'])->where('division_id', $filters['division_id'])
                ->whereBetween('allocation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->get();
            foreach ($allocations as $allocation) {
                $day = $allocation->allocation_date->format('l');
                $key = $day.'|'.$allocation->period_no;
                $cell = $cells->get($key, $allocation->timetableEntry ? $this->regularCell($allocation->timetableEntry) : []);
                $cell['title'] = $cell['title'] ?? $allocation->subject?->subject_name ?? '-';
                $cell['time'] = $cell['time'] ?? ($allocation->timetableEntry ? $this->time($allocation->timetableEntry) : '-');
                $cell['teachers'] = $allocation->substituteTeacher?->name ?? '-';
                $cell['teacher_ids'] = $allocation->substitute_teacher_id ? [(int) $allocation->substitute_teacher_id] : [];
                $cell['original_teacher'] = $allocation->teacher?->name;
                $cell['color'] = '#f3e8ff'; $cell['type'] = 'substitute'; $cell['label'] = 'Substitute Allotted';
                $cells->put($key, $cell);
            }
        }

        $periods = max((int) ($timetable?->total_periods_per_day ?? 0), (int) $cells->keys()->map(fn ($key) => explode('|', $key)[1] ?? 0)->max());

        return [
            'week_start' => $weekStart, 'week_end' => $weekEnd, 'days' => $days, 'cells' => $cells, 'periods' => $periods,
            'academic_year' => AcademicYear::find($filters['academic_year_id'])?->academic_year,
            'grade' => Grade::find($filters['grade_id'])?->grade, 'division' => Division::find($filters['division_id'])?->division,
            'types' => $types, 'has_timetable' => (bool) $timetable,
        ];
    }

    private function regularCell(TimetableEntry $entry): array
    {
        return ['title' => $entry->subject?->subject_name ?? '-', 'teachers' => collect([$entry->teacherOne?->name, $entry->teacherTwo?->name])->filter()->implode(', '), 'teacher_ids' => collect([$entry->teacher_1_id, $entry->teacher_2_id])->filter()->map(fn ($id) => (int) $id)->values()->all(), 'time' => $this->time($entry), 'color' => $entry->subject?->color ?? '#ffffff', 'type' => 'regular', 'label' => 'Regular'];
    }

    private function time(TimetableEntry $entry): string
    {
        return substr((string) $entry->start_time, 0, 5).' - '.substr((string) $entry->end_time, 0, 5);
    }

    private function dateIsWithin(?Carbon $date, $from, $to): bool
    {
        return $date && $date->betweenIncluded($from->startOfDay(), $to->endOfDay());
    }
}
