<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectScheduleRequest;
use App\Models\Project;
use App\Models\ProjectSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProjectScheduleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view.project', only: ['index', 'data', 'show']),
            new Middleware('can:edit.project', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(Project $project): View
    {
        $project->load('category');

        return view('projects.schedules.index', [
            'project' => $project,
            'nextDayNumber' => $this->nextDayNumber($project),
            'minScheduleDate' => $project->start_date?->format('Y-m-d'),
            'maxScheduleDate' => $project->end_date?->format('Y-m-d'),
        ]);
    }

    public function data(Project $project): JsonResponse
    {
        return DataTables::eloquent(
            ProjectSchedule::query()
                ->where('project_id', $project->id)
        )
            ->addIndexColumn()
            ->editColumn('day_number', fn (ProjectSchedule $schedule): string => 'Day '.$schedule->day_number)
            ->editColumn('schedule_date', fn (ProjectSchedule $schedule): string => $schedule->schedule_date?->format('d M Y') ?? '-')
            ->editColumn('description', fn (ProjectSchedule $schedule): string => e($schedule->description ?: '-'))
            ->editColumn('remarks', fn (ProjectSchedule $schedule): string => e($schedule->remarks ?: '-'))
            ->addColumn('actions', fn (ProjectSchedule $schedule): string => $this->actionButtons($project, $schedule))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function show(Project $project, ProjectSchedule $schedule): JsonResponse
    {
        $this->ensureScheduleBelongsToProject($project, $schedule);

        return response()->json([
            'id' => $schedule->id,
            'day_number' => $schedule->day_number,
            'day_label' => 'Day '.$schedule->day_number,
            'schedule_date' => $schedule->schedule_date?->format('Y-m-d'),
            'topic' => $schedule->topic,
            'description' => $schedule->description,
            'remarks' => $schedule->remarks,
        ]);
    }

    public function store(ProjectScheduleRequest $request, Project $project): JsonResponse
    {
        $dayNumber = $this->nextDayNumber($project);
        $this->ensureDayWithinDuration($project, $dayNumber);

        $schedule = $project->schedules()->create([
            ...$request->safe()->only(['schedule_date', 'topic', 'description', 'remarks']),
            'day_number' => $dayNumber,
        ]);

        return response()->json([
            'message' => 'Project schedule saved successfully.',
            'schedule' => $schedule,
            'next_day_number' => $this->nextDayNumber($project),
        ]);
    }

    public function update(ProjectScheduleRequest $request, Project $project, ProjectSchedule $schedule): JsonResponse
    {
        $this->ensureScheduleBelongsToProject($project, $schedule);
        $this->ensureDayWithinDuration($project, $schedule->day_number);

        $schedule->update($request->safe()->only(['schedule_date', 'topic', 'description', 'remarks']));

        return response()->json([
            'message' => 'Project schedule updated successfully.',
            'schedule' => $schedule,
            'next_day_number' => $this->nextDayNumber($project),
        ]);
    }

    public function destroy(Project $project, ProjectSchedule $schedule): JsonResponse
    {
        $this->ensureScheduleBelongsToProject($project, $schedule);
        $schedule->delete();

        return response()->json([
            'message' => 'Project schedule deleted successfully.',
            'next_day_number' => $this->nextDayNumber($project),
        ]);
    }

    private function ensureScheduleBelongsToProject(Project $project, ProjectSchedule $schedule): void
    {
        abort_unless($schedule->project_id === $project->id, 404);
    }

    private function nextDayNumber(Project $project): int
    {
        return ((int) $project->schedules()->max('day_number')) + 1;
    }

    private function ensureDayWithinDuration(Project $project, int $dayNumber): void
    {
        abort_if($dayNumber > $project->duration_days, 422, 'Project duration allows schedules up to Day '.$project->duration_days.'.');
    }

    private function actionButtons(Project $project, ProjectSchedule $schedule): string
    {
        $viewUrl = route('projects.schedules.show', [$project, $schedule]);
        $buttons = sprintf(
            '<button type="button" class="btn-edit project-schedule-view-btn" data-view-url="%s" title="View"><i class="fa-solid fa-eye"></i></button>',
            $viewUrl
        );

        if (request()->user()?->can('edit.project')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-edit project-schedule-edit-btn" data-view-url="%s" data-update-url="%s" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>',
                $viewUrl,
                route('projects.schedules.update', [$project, $schedule])
            );
            $buttons .= sprintf(
                '<button type="button" class="btn-delete project-schedule-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button>',
                route('projects.schedules.destroy', [$project, $schedule])
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }

}
