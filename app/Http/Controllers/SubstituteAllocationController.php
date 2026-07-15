<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubstituteAllocationRequest;
use App\Models\SubstituteAllocation;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Division;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TrainingSchedule;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SubstituteAllocationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view.training-schedule', only: ['index', 'data', 'periods', 'show']),
            new Middleware('can:create.training-schedule', only: ['create', 'store']),
            new Middleware('can:edit.training-schedule', only: ['edit', 'update']),
            new Middleware('can:delete.training-schedule', only: ['destroy']),
        ];
    }

    public function index(TrainingSchedule $trainingSchedule): View
    {
        return view('training-schedules.substitute-allocations.index', compact('trainingSchedule'));
    }

    public function create(TrainingSchedule $trainingSchedule): View
    {
        return view('training-schedules.substitute-allocations.form', [
            ...$this->formOptions($trainingSchedule),
            'allocation' => new SubstituteAllocation(),
        ]);
    }

    public function edit(TrainingSchedule $trainingSchedule, SubstituteAllocation $allocation): View
    {
        $this->ensureAllocation($trainingSchedule, $allocation);
        $allocation->load(['trainerAssignment', 'timetableEntry.timetable.divisions']);

        return view('training-schedules.substitute-allocations.form', [
            ...$this->formOptions($trainingSchedule),
            'allocation' => $allocation,
        ]);
    }

    public function periods(TrainingSchedule $trainingSchedule, Teacher $teacher): JsonResponse
    {
        $datesByDay = collect(CarbonPeriod::create($trainingSchedule->start_date, $trainingSchedule->end_date))
            ->groupBy(fn ($date): string => $date->format('l'));

        $entries = TimetableEntry::query()
            ->with(['timetable.grade', 'timetable.divisions', 'subject'])
            ->where('entry_type', 'period')
            ->where(fn ($query) => $query->where('teacher_1_id', $teacher->id)->orWhere('teacher_2_id', $teacher->id))
            ->whereHas('timetable', fn ($query) => $query
                ->whereDate('applicable_from', '<=', $trainingSchedule->end_date)
                ->whereDate('applicable_to', '>=', $trainingSchedule->start_date))
            ->orderBy('period_no')->get();

        $rows = $entries->flatMap(function (TimetableEntry $entry) use ($datesByDay) {
            return collect($datesByDay->get($entry->day, []))
                ->filter(fn ($date) => $date->betweenIncluded($entry->timetable->applicable_from, $entry->timetable->applicable_to))
                ->map(fn ($date): array => [
                    'timetable_entry_id' => $entry->id,
                    'allocation_date' => $date->toDateString(),
                    'date_label' => $date->format('d M Y'),
                    'day' => $entry->day,
                    'grade' => $entry->timetable->grade?->grade ?? '-',
                    'section' => $entry->timetable->divisions->pluck('division')->implode(', ') ?: '-',
                    'period' => $entry->period_no,
                    'subject' => $entry->subject?->subject_name ?? '-',
                    'timetable_url' => route('timetables.preview', $entry->timetable_id),
                ]);
        })->sortBy(fn (array $row): string => $row['allocation_date'].'|'.str_pad((string) $row['period'], 4, '0', STR_PAD_LEFT))->values();

        return response()->json(['rows' => $rows]);
    }

    public function data(TrainingSchedule $trainingSchedule): JsonResponse
    {
        $query = SubstituteAllocation::query()->where('training_schedule_id', $trainingSchedule->id)
            ->with(['teacher', 'grade', 'division', 'trainerAssignment.teacher', 'timetableEntry.timetable.grade', 'timetableEntry.timetable.divisions', 'timetableEntry.subject', 'substituteTeacher']);

        return DataTables::eloquent($query)->addIndexColumn()
            ->addColumn('date', fn ($row) => $row->allocation_date?->format('d M Y'))
            ->addColumn('trainer', fn ($row) => $row->teacher?->name ?? $row->trainerAssignment?->teacher?->name ?? '-')
            ->addColumn('grade', fn ($row) => $row->grade?->grade ?? $row->timetableEntry?->timetable?->grade?->grade ?? '-')
            ->addColumn('section', fn ($row) => $row->division?->division ?? $row->timetableEntry?->timetable?->divisions?->pluck('division')->implode(', ') ?: '-')
            ->addColumn('subject', fn ($row) => $row->subject?->subject_name ?? $row->timetableEntry?->subject?->subject_name ?? '-')
            ->addColumn('period', fn ($row) => $row->period_no ?? $row->timetableEntry?->period_no ?? '-')
            ->addColumn('substitute', fn ($row) => $row->substituteTeacher?->name ?? '-')
            ->addColumn('actions', fn ($row) => $this->actions($trainingSchedule, $row))
            ->rawColumns(['actions'])->toJson();
    }

    public function show(TrainingSchedule $trainingSchedule, SubstituteAllocation $allocation): View
    {
        $this->ensureAllocation($trainingSchedule, $allocation);
        $allocation->load(['grade', 'division', 'timetableEntry.timetable.grade', 'timetableEntry.timetable.divisions']);
        $allocations = SubstituteAllocation::query()
            ->where('training_schedule_id', $trainingSchedule->id)
            ->with(['teacher', 'subject', 'grade', 'division', 'trainerAssignment.teacher', 'timetableEntry.subject', 'substituteTeacher'])
            ->orderBy('allocation_date')->get();
        $dates = $allocations->pluck('allocation_date')->unique(fn ($date) => $date->toDateString())->values();
        $periods = $allocations->map(fn ($item) => $item->period_no ?? $item->timetableEntry?->period_no)->filter()->unique()->sort()->values();

        return view('training-schedules.substitute-allocations.show', compact(
            'trainingSchedule', 'allocation', 'allocations', 'dates', 'periods'
        ));
    }

    public function store(SubstituteAllocationRequest $request, TrainingSchedule $trainingSchedule): JsonResponse
    {
        DB::transaction(function () use ($request, $trainingSchedule): void {
            foreach ($request->validated('allocations') as $row) {
                $trainingSchedule->substituteAllocations()->create([
                    ...$row, 'training_schedule_trainer_id' => null,
                    'teacher_id' => $request->integer('teacher_id'), 'subject_id' => $request->integer('subject_id'),
                ]);
            }
        });
        return response()->json(['message' => 'Substitute allocation saved successfully.']);
    }

    public function update(SubstituteAllocationRequest $request, TrainingSchedule $trainingSchedule, SubstituteAllocation $allocation): JsonResponse
    {
        $this->ensureAllocation($trainingSchedule, $allocation);
        $row = $request->validated('allocations')[0];
        $allocation->update([...$row, 'training_schedule_trainer_id' => null, 'teacher_id' => $request->integer('teacher_id'), 'subject_id' => $request->integer('subject_id')]);
        return response()->json(['message' => 'Substitute allocation updated successfully.']);
    }

    public function destroy(TrainingSchedule $trainingSchedule, SubstituteAllocation $allocation): JsonResponse
    {
        $this->ensureAllocation($trainingSchedule, $allocation);
        $allocation->delete();
        return response()->json(['message' => 'Substitute allocation deleted successfully.']);
    }

    private function ensureAllocation(TrainingSchedule $schedule, SubstituteAllocation $allocation): void
    {
        abort_unless($allocation->training_schedule_id === $schedule->id, 404);
    }

    private function actions(TrainingSchedule $schedule, SubstituteAllocation $allocation): string
    {
        $buttons = sprintf('<a href="%s" class="btn-view" title="View Timetable"><i class="fa-solid fa-eye"></i></a>', route('training-schedules.substitute-allocations.show', [$schedule, $allocation]));
        if (request()->user()?->can('edit.training-schedule')) {
            $buttons .= sprintf('<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>', route('training-schedules.substitute-allocations.edit', [$schedule, $allocation]));
        }
        if (request()->user()?->can('delete.training-schedule')) {
            $buttons .= sprintf('<button class="btn-delete border-0 substitute-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button>', route('training-schedules.substitute-allocations.destroy', [$schedule, $allocation]));
        }
        return '<div class="action-btns">'.$buttons.'</div>';
    }

    private function formOptions(TrainingSchedule $trainingSchedule): array
    {
        $trainingSchedule->load(['trainerAssignments.teacher', 'trainerAssignments.subject']);
        $workingDays = collect(CarbonPeriod::create($trainingSchedule->start_date, $trainingSchedule->end_date))
            ->filter(fn ($date): bool => $date->format('l') !== 'Sunday')
            ->map(fn ($date): string => $date->format('l'))
            ->unique()
            ->sortBy(fn (string $day): int => (int) array_search($day, TimetableEntry::DAYS, true))
            ->values();

        return [
            'trainingSchedule' => $trainingSchedule,
            'subjects' => Subject::query()->active()->orderBy('subject_name')->get(),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'grades' => Grade::query()->active()->orderBy('grade')->get(['id', 'grade']),
            'divisions' => Division::query()->active()->orderBy('division')->get(['id', 'grade_id', 'division']),
            'workingDays' => $workingDays,
            'substituteTeachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
        ];
    }
}
