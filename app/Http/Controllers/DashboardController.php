<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\ProjectWeek;
use App\Models\SpecialEventTimetableEntry;
use App\Models\SubstituteAllocation;
use App\Models\TrainingSchedule;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(
        TeacherSchedulerController $scheduler,
        GeneratedTimetableController $timetableController,
    ): View
    {
        $today = today();
        $teacher = request()->user()->teacher;

        if ($teacher) {
            $academicYear = AcademicYear::query()->active()->latest('start_date')->first();
            $weekStart = now()->startOfWeek(Carbon::MONDAY);
            $regularPreview = $scheduler->buildPreview(
                $teacher, $timetableController, $weekStart, $weekStart->copy()->addDays(6), $academicYear?->id, 'regular'
            );
            $schedulePreviews = collect();

            $projectWeek = ProjectWeek::query()
                ->when($academicYear, fn ($query) => $query->where('academic_year_id', $academicYear->id))
                ->where('status', 'publish')
                ->whereHas('entries', fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
                ->orderByRaw('applicable_to >= ? DESC', [today()->toDateString()])
                ->orderBy('applicable_from')
                ->first();
            if ($projectWeek) {
                $projectStart = $projectWeek->applicable_from->copy()->startOfWeek(Carbon::MONDAY);
                $schedulePreviews->push([
                    'title' => 'Project Week',
                    'preview' => $scheduler->buildPreview($teacher, $timetableController, $projectStart, $projectStart->copy()->addDays(6), $academicYear?->id, 'project'),
                ]);
            }

            $specialEntry = SpecialEventTimetableEntry::query()
                ->with('specialEvent')
                ->where('is_event_period', true)
                ->when($academicYear, fn ($query) => $query->whereHas('specialEvent', fn ($eventQuery) => $eventQuery->where('academic_year_id', $academicYear->id)))
                ->get()
                ->filter(fn ($entry) => collect($entry->teacher_names)->contains(
                    fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
                ))
                ->sortBy(fn ($entry) => $entry->specialEvent?->event_end_date?->isPast() ? 1 : 0)
                ->first();
            if ($specialEntry?->specialEvent) {
                $event = $specialEntry->specialEvent;
                $eventStart = $event->event_start_date->copy()->startOfWeek(Carbon::MONDAY);
                $schedulePreviews->push([
                    'title' => 'Special Event - '.$event->event_title,
                    'preview' => $scheduler->buildPreview($teacher, $timetableController, $eventStart, $eventStart->copy()->addDays(6), $academicYear?->id, 'special'),
                ]);
            }

            $training = TrainingSchedule::query()->where('status', 'published')
                ->whereHas('trainerAssignments', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->orderByRaw('end_date >= ? DESC', [today()->toDateString()])
                ->orderBy('start_date')->first();
            if ($training) {
                $trainingStart = $training->start_date->copy()->startOfWeek(Carbon::MONDAY);
                $schedulePreviews->push([
                    'title' => 'Training Schedule - '.$training->title,
                    'preview' => $scheduler->buildPreview($teacher, $timetableController, $trainingStart, $trainingStart->copy()->addDays(6), $academicYear?->id, 'training'),
                ]);
            }

            $substitute = SubstituteAllocation::query()->where('substitute_teacher_id', $teacher->id)
                ->orderByRaw('allocation_date >= ? DESC', [today()->toDateString()])
                ->orderBy('allocation_date')->first();
            if ($substitute?->allocation_date) {
                $substituteWeek = $substitute->allocation_date->copy()->startOfWeek(Carbon::MONDAY);
                $schedulePreviews->push([
                    'title' => 'Training Substitute Allocation',
                    'preview' => $scheduler->buildPreview($teacher, $timetableController, $substituteWeek, $substituteWeek->copy()->addDays(6), $academicYear?->id, 'substitute'),
                ]);
            }

            return view('teachers.portal.dashboard', compact('teacher', 'academicYear', 'regularPreview', 'schedulePreviews'));
        }

        return view('dashboard', [
            'currentAcademicYear' => AcademicYear::query()
                ->where('is_active', true)
                ->orderByDesc('start_date')
                ->value('academic_year') ?? 'Not set',
            'activeGrades' => Grade::query()->where('is_active', true)->count(),
            'totalDivisions' => Division::query()->count(),
            'totalSubjects' => Subject::query()->count(),
            'totalTeachers' => Teacher::query()->count(),
            'publishedTimetables' => Timetable::query()->where('status', 'published')->count(),
            'draftTimetables' => Timetable::query()->where('status', 'draft')->count(),
            'pendingApprovals' => Teacher::query()->where('is_verified', false)->count(),
            'todaysClasses' => TimetableEntry::query()
                ->where('day', $today->format('l'))
                ->where('entry_type', 'period')
                ->whereHas('timetable', fn ($query) => $query
                    ->where('status', 'published')
                    ->whereDate('applicable_from', '<=', $today)
                    ->whereDate('applicable_to', '>=', $today))
                ->count(),
        ]);
    }
}
