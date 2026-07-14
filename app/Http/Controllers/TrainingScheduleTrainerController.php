<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainingScheduleTrainerRequest;
use App\Models\Designation;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TrainingSchedule;
use App\Models\TrainingScheduleTrainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TrainingScheduleTrainerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view.training-schedule', only: ['index', 'data', 'show']),
            new Middleware('can:create.training-schedule', only: ['store']),
            new Middleware('can:edit.training-schedule', only: ['update']),
            new Middleware('can:delete.training-schedule', only: ['destroy']),
        ];
    }

    public function index(TrainingSchedule $trainingSchedule): View
    {
        $trainingSchedule->load(['trainerType', 'trainerCategory']);

        return view('training-schedules.trainers.index', [
            'trainingSchedule' => $trainingSchedule,
            'designations' => Designation::query()->active()->orderBy('designation_name')->get(['id', 'designation_name']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'designation_id']),
            'subjects' => Subject::query()->active()->with('grade')->orderBy('subject_name')->get(),
        ]);
    }

    public function data(TrainingSchedule $trainingSchedule): JsonResponse
    {
        return DataTables::eloquent(
            TrainingScheduleTrainer::query()
                ->with(['designation', 'teacher', 'subject'])
                ->where('training_schedule_id', $trainingSchedule->id),
        )
            ->addIndexColumn()
            ->filter(function ($query): void {
                $keyword = request()->input('search.value');

                if (! $keyword) {
                    return;
                }

                $query->where(function ($query) use ($keyword): void {
                    $query->whereHas('designation', fn ($query) => $query->where('designation_name', 'like', "%{$keyword}%"))
                        ->orWhereHas('teacher', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('subject', fn ($query) => $query->where('subject_name', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('designation', fn (TrainingScheduleTrainer $trainer): string => $trainer->designation?->designation_name ?? '-')
            ->addColumn('name', fn (TrainingScheduleTrainer $trainer): string => $trainer->teacher?->name ?? '-')
            ->addColumn('subject', fn (TrainingScheduleTrainer $trainer): string => $trainer->subject?->subject_name ?? '-')
            ->addColumn('actions', fn (TrainingScheduleTrainer $trainer): string => $this->actionButtons($trainingSchedule, $trainer))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function show(TrainingSchedule $trainingSchedule, TrainingScheduleTrainer $trainer): JsonResponse
    {
        $this->ensureBelongsToSchedule($trainingSchedule, $trainer);

        return response()->json([
            'id' => $trainer->id,
            'designation_id' => $trainer->designation_id,
            'teacher_id' => $trainer->teacher_id,
            'subject_id' => $trainer->subject_id,
        ]);
    }

    public function store(TrainingScheduleTrainerRequest $request, TrainingSchedule $trainingSchedule): JsonResponse
    {
        $trainer = $trainingSchedule->trainerAssignments()->create($request->validated());

        return response()->json([
            'message' => 'Trainer added successfully.',
            'trainer' => $trainer,
        ]);
    }

    public function update(
        TrainingScheduleTrainerRequest $request,
        TrainingSchedule $trainingSchedule,
        TrainingScheduleTrainer $trainer,
    ): JsonResponse {
        $this->ensureBelongsToSchedule($trainingSchedule, $trainer);
        $trainer->update($request->validated());

        return response()->json([
            'message' => 'Trainer updated successfully.',
            'trainer' => $trainer,
        ]);
    }

    public function destroy(TrainingSchedule $trainingSchedule, TrainingScheduleTrainer $trainer): JsonResponse
    {
        $this->ensureBelongsToSchedule($trainingSchedule, $trainer);
        $trainer->delete();

        return response()->json(['message' => 'Trainer deleted successfully.']);
    }

    private function ensureBelongsToSchedule(
        TrainingSchedule $trainingSchedule,
        TrainingScheduleTrainer $trainer,
    ): void {
        abort_unless($trainer->training_schedule_id === $trainingSchedule->id, 404);
    }

    private function actionButtons(
        TrainingSchedule $trainingSchedule,
        TrainingScheduleTrainer $trainer,
    ): string {
        $buttons = '';

        if (request()->user()?->can('edit.training-schedule')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-edit training-trainer-edit-btn" data-view-url="%s" data-update-url="%s" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>',
                route('training-schedules.trainers.show', [$trainingSchedule, $trainer]),
                route('training-schedules.trainers.update', [$trainingSchedule, $trainer]),
            );
        }

        if (request()->user()?->can('delete.training-schedule')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-delete border-0 training-trainer-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button>',
                route('training-schedules.trainers.destroy', [$trainingSchedule, $trainer]),
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
