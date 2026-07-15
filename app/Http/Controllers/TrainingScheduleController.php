<?php

namespace App\Http\Controllers;

use App\Exports\TrainingSchedulesExport;
use App\Http\Requests\TrainingScheduleRequest;
use App\Models\TrainingSchedule;
use App\Services\TrainingScheduleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class TrainingScheduleController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TrainingScheduleService $trainingScheduleService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.training-schedule', only: ['index', 'data', 'show', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.training-schedule', only: ['create', 'store']),
            new Middleware('can:edit.training-schedule', only: ['edit', 'update']),
            new Middleware('can:delete.training-schedule', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('training-schedules.index', $this->formOptions());
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->trainingScheduleService->query($request->only([
            'status',
            'trainer_type_id',
            'trainer_category_id',
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
                        ->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhere('resource_person_trainer', 'like', "%{$keyword}%")
                        ->orWhere('venue', 'like', "%{$keyword}%")
                        ->orWhereHas('trainerType', fn ($query) => $query->where('title', 'like', "%{$keyword}%"))
                        ->orWhereHas('trainerCategory', fn ($query) => $query->where('title', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn (TrainingSchedule $trainingSchedule): string => sprintf(
                '<input type="checkbox" class="training-schedule-row-check" value="%d">',
                $trainingSchedule->id,
            ))
            ->addColumn('category', fn (TrainingSchedule $trainingSchedule): string => $trainingSchedule->trainerCategory?->title ?? '-')
            ->addColumn('type', fn (TrainingSchedule $trainingSchedule): string => $trainingSchedule->trainerType?->title ?? '-')
            ->editColumn('start_date', fn (TrainingSchedule $trainingSchedule): string => $trainingSchedule->start_date?->format('d M Y') ?? '-')
            ->editColumn('end_date', fn (TrainingSchedule $trainingSchedule): string => $trainingSchedule->end_date?->format('d M Y') ?? '-')
            ->editColumn('status', fn (TrainingSchedule $trainingSchedule): string => sprintf(
                '<span class="%s">%s</span>',
                $trainingSchedule->status === 'published' ? 'status-green' : 'status-red',
                TrainingSchedule::STATUSES[$trainingSchedule->status] ?? ucfirst($trainingSchedule->status),
            ))
            ->addColumn('actions', fn (TrainingSchedule $trainingSchedule): string => $this->actionButtons($trainingSchedule))
            ->rawColumns(['select', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('training-schedules.form', [
            ...$this->formOptions(),
            'trainingSchedule' => new TrainingSchedule([
                'code' => $this->trainingScheduleService->nextCode(),
                'status' => 'draft',
            ]),
            'selectedSubjectIds' => collect(old('subject_ids', [])),
            'scheduleSessions' => collect(old('sessions', [[]])),
        ]);
    }

    public function store(TrainingScheduleRequest $request): RedirectResponse
    {
        $this->trainingScheduleService->create($request->validated());

        return redirect()->route('training-schedules.index')
            ->with('success', 'Training schedule saved successfully.');
    }

    public function show(TrainingSchedule $trainingSchedule): View
    {
        $trainingSchedule->load(['trainerType', 'trainerCategory', 'subjects.grade', 'sessions', 'creator']);

        return view('training-schedules.show', compact('trainingSchedule'));
    }

    public function edit(TrainingSchedule $trainingSchedule): View
    {
        $trainingSchedule->load(['subjects', 'sessions', 'creator']);

        return view('training-schedules.form', [
            ...$this->formOptions(),
            'trainingSchedule' => $trainingSchedule,
            'selectedSubjectIds' => collect(old('subject_ids', $trainingSchedule->subjects->pluck('id')->all())),
            'scheduleSessions' => collect(old('sessions', $trainingSchedule->sessions->map(fn ($session): array => [
                'session_date' => $session->session_date?->toDateString(),
                'time_from' => substr((string) $session->time_from, 0, 5),
                'time_to' => substr((string) $session->time_to, 0, 5),
                'topic_module' => $session->topic_module,
                'duration_hours' => $session->duration_hours,
            ])->all())),
        ]);
    }

    public function update(TrainingScheduleRequest $request, TrainingSchedule $trainingSchedule): RedirectResponse
    {
        $this->trainingScheduleService->update($trainingSchedule, $request->validated());

        return redirect()->route('training-schedules.index')
            ->with('success', 'Training schedule updated successfully.');
    }

    public function destroy(Request $request, TrainingSchedule $trainingSchedule): JsonResponse|RedirectResponse
    {
        $this->trainingScheduleService->delete($trainingSchedule);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Training schedule deleted successfully.']);
        }

        return redirect()->route('training-schedules.index')
            ->with('success', 'Training schedule deleted successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $trainingSchedules = $this->selectedTrainingSchedules($request);

        if ($trainingSchedules->isEmpty()) {
            return back()->with('error', 'Select at least one training schedule to export.');
        }

        return Excel::download(new TrainingSchedulesExport($trainingSchedules), 'training-schedules.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $trainingSchedules = $this->selectedTrainingSchedules($request);

        if ($trainingSchedules->isEmpty()) {
            return back()->with('error', 'Select at least one training schedule to export.');
        }

        return Pdf::loadView('training-schedules.export-pdf', compact('trainingSchedules'))
            ->download('training-schedules.pdf');
    }

    private function selectedTrainingSchedules(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()->map(fn ($id): int => (int) $id)->unique()->values()->all();

        return $this->trainingScheduleService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'trainerTypes' => $this->trainingScheduleService->trainerTypes(),
            'trainerCategories' => $this->trainingScheduleService->trainerCategories(),
            'subjects' => $this->trainingScheduleService->subjects(),
            'conductedByOptions' => TrainingSchedule::CONDUCTED_BY_OPTIONS,
            'modes' => TrainingSchedule::MODES,
            'applicableOptions' => TrainingSchedule::APPLICABLE_OPTIONS,
            'statuses' => TrainingSchedule::STATUSES,
        ];
    }

    private function actionButtons(TrainingSchedule $trainingSchedule): string
    {
        $buttons = sprintf(
            '<a href="%s" class="btn-view"><i class="fa-solid fa-eye"></i></a>',
            route('training-schedules.show', $trainingSchedule),
        );
        $buttons .= sprintf(
            '<a href="%s" class="btn-view training-schedule-trainers-btn" title="Manage Trainers"><i class="fa-solid fa-user"></i></a>',
            route('training-schedules.trainers.index', $trainingSchedule),
        );

        if (request()->user()?->can('edit.training-schedule')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('training-schedules.edit', $trainingSchedule),
            );
        }

        if (request()->user()?->can('delete.training-schedule')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-delete border-0 training-schedule-delete-btn" data-delete-url="%s"><i class="fa-solid fa-trash"></i></button>',
                route('training-schedules.destroy', $trainingSchedule),
            );
        }

        $buttons .= sprintf(
            '<div class="dropdown">
                <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="%s">Substitute Allocation</a></li>
                </ul>
            </div>',
            route('training-schedules.substitute-allocations.index', $trainingSchedule),
        );

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
