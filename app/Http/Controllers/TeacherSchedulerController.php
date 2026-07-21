<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\ProjectWeek;
use App\Models\SpecialEventTimetableEntry;
use App\Models\SubstituteAllocation;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
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

    public function buildPreview(Teacher $teacher, GeneratedTimetableController $timetableController, ?Carbon $rangeStart = null, ?Carbon $rangeEnd = null): array
    {
        $weekStart = ($rangeStart ?? now()->startOfWeek(Carbon::MONDAY))->copy()->startOfDay();
        $weekEnd = ($rangeEnd ?? $weekStart->copy()->addDays(6))->copy()->endOfDay();
        $days = collect(TimetableEntry::DAYS)->mapWithKeys(fn ($day, $index) => [$day => $weekStart->copy()->addDays($index)]);
        $combinations = collect();

        Timetable::query()->with('divisions:id,grade_id')
            ->whereDate('applicable_from', '<=', $weekEnd)->whereDate('applicable_to', '>=', $weekStart)
            ->whereHas('entries', fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
            ->get()->each(function (Timetable $timetable) use ($combinations): void {
                foreach ($timetable->divisions as $division) {
                    $combinations->put($timetable->grade_id.'|'.$division->id, [$timetable->grade_id, $division->id]);
                }
            });

        ProjectWeek::query()->with('divisions:id,grade_id')
            ->whereDate('applicable_from', '<=', $weekEnd)->whereDate('applicable_to', '>=', $weekStart)
            ->whereHas('entries', fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
            ->get()->each(function (ProjectWeek $projectWeek) use ($combinations): void {
                foreach ($projectWeek->divisions as $division) {
                    $combinations->put($projectWeek->grade_id.'|'.$division->id, [$projectWeek->grade_id, $division->id]);
                }
            });

        SpecialEventTimetableEntry::query()->with('specialEvent')->where('is_event_period', true)
            ->whereHas('specialEvent', fn ($query) => $query->whereDate('event_start_date', '<=', $weekEnd)->whereDate('event_end_date', '>=', $weekStart))
            ->get()->filter(fn (SpecialEventTimetableEntry $entry) => collect($entry->teacher_names)->contains(
                fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
            ))->each(fn (SpecialEventTimetableEntry $entry) => $combinations->put($entry->grade_id.'|'.$entry->division_id, [$entry->grade_id, $entry->division_id]));

        SubstituteAllocation::query()->where('substitute_teacher_id', $teacher->id)
            ->whereBetween('allocation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()->each(fn (SubstituteAllocation $allocation) => $combinations->put($allocation->grade_id.'|'.$allocation->division_id, [$allocation->grade_id, $allocation->division_id]));

        $cells = collect();
        foreach ($combinations->values() as [$gradeId, $divisionId]) {
            $grade = Grade::find($gradeId);
            if (! $grade || ! $divisionId) continue;

            $classPreview = $timetableController->buildPreview([
                'academic_year_id' => $grade->academic_year_id,
                'grade_id' => $grade->id,
                'division_id' => $divisionId,
                'types' => ['regular', 'project', 'special', 'substitute'],
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

        $periods = (int) $cells->keys()->map(fn ($key) => explode('|', $key)[1] ?? 0)->max();

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'cells' => $cells,
            'periods' => $periods,
        ];
    }

    private function belongsToTeacher(array $cell, Teacher $teacher): bool
    {
        return in_array($teacher->id, $cell['teacher_ids'] ?? [], true)
            || collect($cell['teacher_names'] ?? [])->contains(
                fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
            );
    }
}
