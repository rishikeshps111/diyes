<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ProjectWeek;
use App\Models\SpecialEventTimetableEntry;
use App\Models\SubstituteAllocation;
use App\Models\Teacher;
use App\Models\TrainingSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class TeacherAllotmentController extends Controller implements HasMiddleware
{
    private const TIMETABLE_TYPES = [
        'regular' => 'Regular',
        'project' => 'Project Week',
        'training' => 'Training Schedule',
        'substitute' => 'Substitute Allocation',
        'special' => 'Special Events',
    ];

    public static function middleware(): array { return [new Middleware('can:view.teacher')]; }

    public function index(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated): View
    {
        [$filters, $teacher, $previews] = $this->result($request, $scheduler, $generated, false);

        return view('teacher-allotments.index', [
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'timetableTypes' => self::TIMETABLE_TYPES,
            'teachers' => $this->teachers(),
            'filters' => $filters,
            'teacher' => $teacher,
            'previews' => $previews,
        ]);
    }

    public function pdf(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated)
    {
        [$filters, $teacher, $previews] = $this->result($request, $scheduler, $generated, true);

        return Pdf::loadView('teacher-allotments.pdf', compact('filters', 'teacher', 'previews'))->setPaper('a4', 'landscape')
            ->download('teacher-work-load-'.str($teacher->name)->slug().'.pdf');
    }

    private function result(Request $request, TeacherSchedulerController $scheduler, GeneratedTimetableController $generated, bool $required): array
    {
        $rule = $required ? 'required' : 'nullable';
        $validated = $request->validate([
            'academic_year_id' => [$rule, 'integer', 'exists:academic_years,id'],
            'teacher_id' => [$rule, 'integer', 'exists:teachers,id'],
            'timetable_type' => [$rule, 'string', 'in:'.implode(',', array_keys(self::TIMETABLE_TYPES))],
            'from_date' => ['nullable', 'date', 'required_with:to_date'],
            'to_date' => ['nullable', 'date', 'required_with:from_date', 'after_or_equal:from_date'],
        ]);

        $activeAcademicYearId = AcademicYear::query()->active()->orderByDesc('start_date')->value('id');
        $filters = [
            'academic_year_id' => $validated['academic_year_id'] ?? $activeAcademicYearId,
            'teacher_id' => $validated['teacher_id'] ?? null,
            'timetable_type' => $validated['timetable_type'] ?? 'regular',
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];

        $teacher = !empty($filters['teacher_id']) ? Teacher::find($filters['teacher_id']) : null;
        $previews = collect();

        if ($teacher && $filters['academic_year_id']) {
            if ($filters['timetable_type'] === 'regular') {
                $start = now()->startOfWeek(Carbon::MONDAY);
                $preview = $scheduler->buildPreview(
                    $teacher,
                    $generated,
                    $start,
                    $start->copy()->addDays(6),
                    (int) $filters['academic_year_id'],
                    'regular',
                );
                $preview['show_dates'] = false;
                $previews->push($preview);

                return [$filters, $teacher, $previews];
            }

            $hasDateRange = !empty($filters['from_date']) && !empty($filters['to_date']);
            $from = $hasDateRange ? Carbon::parse($filters['from_date'])->startOfDay() : null;
            $to = $hasDateRange ? Carbon::parse($filters['to_date'])->endOfDay() : null;

            if ($hasDateRange) {
                abort_if($from->diffInDays($to) > 92, 422, 'The date range may not exceed 93 days.');
                $weeks = collect(CarbonPeriod::create($from->copy()->startOfWeek(), '1 week', $to))
                    ->map(fn ($week) => Carbon::parse($week)->startOfWeek(Carbon::MONDAY));
            } else {
                $weeks = $this->assignedWeeks(
                    $teacher,
                    (int) $filters['academic_year_id'],
                    $filters['timetable_type'],
                );
            }

            foreach ($weeks as $week) {
                $start = $week->copy()->startOfWeek(Carbon::MONDAY);
                $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
                $preview = $scheduler->buildPreview(
                    $teacher,
                    $generated,
                    $start,
                    $end,
                    (int) $filters['academic_year_id'],
                    $filters['timetable_type'],
                );

                if ($hasDateRange) {
                    $validDays = $preview['days']
                        ->filter(fn (Carbon $date) => $date->betweenIncluded($from, $to))
                        ->keys();
                    $preview['cells'] = $preview['cells']
                        ->filter(fn ($entries, $key) => $validDays->contains(explode('|', $key)[0]));
                }

                $preview['show_dates'] = true;

                if ($preview['cells']->isNotEmpty()) {
                    $previews->push($preview);
                }
            }
        }

        return [$filters, $teacher, $previews];
    }

    private function assignedWeeks(Teacher $teacher, int $academicYearId, string $timetableType)
    {
        $dates = match ($timetableType) {
            'project' => ProjectWeek::query()
                ->where('academic_year_id', $academicYearId)
                ->whereHas('entries', fn ($query) => $query
                    ->where('teacher_1_id', $teacher->id)
                    ->orWhere('teacher_2_id', $teacher->id))
                ->get(['applicable_from', 'applicable_to'])
                ->map(fn (ProjectWeek $projectWeek) => [$projectWeek->applicable_from, $projectWeek->applicable_to]),
            'training' => TrainingSchedule::query()
                ->whereHas('trainerAssignments', fn ($query) => $query->where('teacher_id', $teacher->id))
                ->with(['sessions' => fn ($query) => $query->select('id', 'training_schedule_id', 'session_date')])
                ->get()
                ->flatMap(fn (TrainingSchedule $schedule) => $schedule->sessions
                    ->map(fn ($session) => [$session->session_date, $session->session_date])),
            'special' => SpecialEventTimetableEntry::query()
                ->with('specialEvent:id,academic_year_id,event_start_date,event_end_date')
                ->where('is_event_period', true)
                ->whereHas('specialEvent', fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->get()
                ->filter(fn (SpecialEventTimetableEntry $entry) => collect($entry->teacher_names)->contains(
                    fn ($name) => strcasecmp(trim((string) $name), trim($teacher->name)) === 0
                ))
                ->map(fn (SpecialEventTimetableEntry $entry) => [
                    $entry->specialEvent->event_start_date,
                    $entry->specialEvent->event_end_date,
                ]),
            'substitute' => SubstituteAllocation::query()
                ->where('substitute_teacher_id', $teacher->id)
                ->whereHas('grade', fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->get(['allocation_date'])
                ->map(fn (SubstituteAllocation $allocation) => [$allocation->allocation_date, $allocation->allocation_date]),
            default => collect(),
        };

        return $dates
            ->flatMap(function (array $range) {
                return collect(CarbonPeriod::create(
                    Carbon::parse($range[0])->startOfWeek(Carbon::MONDAY),
                    '1 week',
                    Carbon::parse($range[1])->endOfWeek(Carbon::SUNDAY),
                ));
            })
            ->map(fn ($week) => Carbon::parse($week)->startOfWeek(Carbon::MONDAY))
            ->unique(fn (Carbon $week) => $week->toDateString())
            ->sort()
            ->values();
    }

    private function teachers() { return Teacher::query()->orderBy('name')->get(['id', 'name', 'employee_id']); }
}
