<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\ProjectWeek;
use App\Models\SpecialEventTimetableEntry;
use App\Models\SubstituteAllocation;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\TrainingSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TeacherSchedulerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:view.teacher')];
    }

    public function index(Teacher $teacher, GeneratedTimetableController $timetableController): View
    {
        return view('teachers.scheduler.index', [
            'teacher' => $teacher,
            'preview' => $this->buildPreview($teacher, $timetableController),
        ]);
    }

    public function pdf(Teacher $teacher, GeneratedTimetableController $timetableController)
    {
        $preview = $this->buildPreview($teacher, $timetableController);

        return Pdf::loadView('teachers.scheduler.pdf', compact('teacher', 'preview'))
            ->setPaper('a4', 'landscape')
            ->download('teacher-scheduler-'.str($teacher->name)->slug().'-'.$preview['week_start']->format('Y-m-d').'.pdf');
    }

    public function buildPreview(
        Teacher $teacher,
        GeneratedTimetableController $timetableController,
        ?Carbon $rangeStart = null,
        ?Carbon $rangeEnd = null,
        ?int $academicYearId = null,
        ?string $timetableType = null,
    ): array
    {
        $weekStart = ($rangeStart ?? now()->startOfWeek(Carbon::MONDAY))->copy()->startOfDay();
        $weekEnd = ($rangeEnd ?? $weekStart->copy()->addDays(6))->copy()->endOfDay();
        $days = collect(TimetableEntry::DAYS)->mapWithKeys(fn ($day, $index) => [$day => $weekStart->copy()->addDays($index)]);
        $combinations = collect();
        $include = function (string $type) use ($timetableType): bool {
            if ($timetableType === null) {
                return true;
            }

            return $type === $timetableType;
        };

        if ($include('regular')) {
            Timetable::query()->with('divisions:id,grade_id')
                ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->when($timetableType !== 'regular', fn ($query) => $query
                    ->whereDate('applicable_from', '<=', $weekEnd)
                    ->whereDate('applicable_to', '>=', $weekStart))
                ->whereHas('entries', fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
                ->get()->each(function (Timetable $timetable) use ($combinations): void {
                    foreach ($timetable->divisions as $division) {
                        $combinations->put($timetable->grade_id.'|'.$division->id, [$timetable->grade_id, $division->id]);
                    }
                });
        }

        if ($include('project')) {
            ProjectWeek::query()->with('divisions:id,grade_id')
                ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->whereDate('applicable_from', '<=', $weekEnd)->whereDate('applicable_to', '>=', $weekStart)
                ->whereHas('entries', fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
                ->get()->each(function (ProjectWeek $projectWeek) use ($combinations): void {
                    foreach ($projectWeek->divisions as $division) {
                        $combinations->put($projectWeek->grade_id.'|'.$division->id, [$projectWeek->grade_id, $division->id]);
                    }
                });
        }

        if ($include('special')) {
            SpecialEventTimetableEntry::query()->with('specialEvent')->where('is_event_period', true)
                ->whereHas('specialEvent', fn ($query) => $query
                    ->when($academicYearId, fn ($eventQuery) => $eventQuery->where('academic_year_id', $academicYearId))
                    ->whereDate('event_start_date', '<=', $weekEnd)
                    ->whereDate('event_end_date', '>=', $weekStart))
                ->get()->filter(fn (SpecialEventTimetableEntry $entry) => collect($entry->teacher_names)->contains(
                    fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
                ))->each(fn (SpecialEventTimetableEntry $entry) => $combinations->put($entry->grade_id.'|'.$entry->division_id, [$entry->grade_id, $entry->division_id]));
        }

        if ($include('substitute')) {
            SubstituteAllocation::query()->where('substitute_teacher_id', $teacher->id)
                ->when($academicYearId, fn ($query) => $query
                    ->whereHas('grade', fn ($gradeQuery) => $gradeQuery->where('academic_year_id', $academicYearId)))
                ->whereBetween('allocation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()->each(fn (SubstituteAllocation $allocation) => $combinations->put($allocation->grade_id.'|'.$allocation->division_id, [$allocation->grade_id, $allocation->division_id]));
        }

        $cells = collect();
        foreach ($combinations->values() as [$gradeId, $divisionId]) {
            $grade = Grade::find($gradeId);
            if (! $grade || ! $divisionId) continue;

            $classPreview = $timetableController->buildPreview([
                'academic_year_id' => $grade->academic_year_id,
                'grade_id' => $grade->id,
                'division_id' => $divisionId,
                'types' => collect(['regular', 'project', 'special', 'substitute'])
                    ->filter(fn (string $type): bool => $include($type))
                    ->values()
                    ->all(),
                'ignore_regular_dates' => $timetableType === 'regular',
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
            ]);

            foreach ($classPreview['cells'] as $key => $cell) {
                if (! $this->belongsToTeacher($cell, $teacher)) continue;
                $cell['grade'] = $classPreview['grade'];
                $cell['division'] = $classPreview['division'];
                $cells->put($key, collect($cells->get($key, []))->push($cell)->all());
            }
        }

        if ($include('training')) {
            $this->appendTrainingSchedules($cells, $teacher, $weekStart, $weekEnd);
        }

        $periods = (int) $cells->keys()->map(fn ($key) => explode('|', $key)[1] ?? 0)->max();

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'cells' => $cells,
            'periods' => $periods,
        ];
    }

    private function appendTrainingSchedules($cells, Teacher $teacher, Carbon $weekStart, Carbon $weekEnd): void
    {
        TrainingSchedule::query()
            ->with([
                'sessions',
                'trainerAssignments' => fn ($query) => $query
                    ->where('teacher_id', $teacher->id)
                    ->with('subject'),
            ])
            ->where('status', 'published')
            ->whereDate('start_date', '<=', $weekEnd)
            ->whereDate('end_date', '>=', $weekStart)
            ->whereHas('trainerAssignments', fn ($query) => $query->where('teacher_id', $teacher->id))
            ->get()
            ->each(function (TrainingSchedule $schedule) use ($cells, $weekStart, $weekEnd): void {
                $subjects = $schedule->trainerAssignments
                    ->pluck('subject.subject_name')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                foreach ($schedule->sessions as $session) {
                    if (! $session->session_date?->betweenIncluded($weekStart, $weekEnd)) {
                        continue;
                    }

                    $day = $session->session_date->format('l');
                    $period = max(1, (int) $session->session_no);
                    $key = $day.'|'.$period;
                    $cell = [
                        'title' => $schedule->title,
                        'teachers' => $schedule->resource_person_trainer,
                        'teacher_ids' => $schedule->trainerAssignments->pluck('teacher_id')->map(fn ($id) => (int) $id)->all(),
                        'time' => substr((string) $session->time_from, 0, 5).' - '.substr((string) $session->time_to, 0, 5),
                        'color' => '#fef3c7',
                        'type' => 'training',
                        'label' => 'Training Schedule',
                        'grade' => null,
                        'division' => null,
                        'meta' => collect([$subjects, $session->topic_module])->filter()->implode(' / '),
                    ];

                    $cells->put($key, collect($cells->get($key, []))->push($cell)->all());
                }
            });
    }

    private function belongsToTeacher(array $cell, Teacher $teacher): bool
    {
        return in_array($teacher->id, $cell['teacher_ids'] ?? [], true)
            || collect($cell['teacher_names'] ?? [])->contains(
                fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
            );
    }
}
