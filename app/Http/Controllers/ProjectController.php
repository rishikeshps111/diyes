<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsExport;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectStatusRequest;
use App\Models\Project;
use App\Services\ProjectService;
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

class ProjectController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ProjectService $projectService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.project', only: ['index', 'data', 'show', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.project', only: ['create', 'store']),
            new Middleware('can:edit.project', only: ['edit', 'update', 'updateStatus']),
            new Middleware('can:delete.project', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('projects.index', [
            'categories' => $this->projectService->categories(),
            'grades' => $this->projectService->grades(),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->projectService->query($request->only([
            'project_category_id',
            'grade_id',
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
                    $query->where('project_code', 'like', "%{$keyword}%")
                        ->orWhere('project_title', 'like', "%{$keyword}%")
                        ->orWhere('venue', 'like', "%{$keyword}%")
                        ->orWhereHas('category', fn($categoryQuery) => $categoryQuery->where('title', 'like', "%{$keyword}%"))
                        ->orWhereHas('grades', fn($gradeQuery) => $gradeQuery->where('grade', 'like', "%{$keyword}%"))
                        ->orWhereHas('subjects', fn($subjectQuery) => $subjectQuery->where('subject_name', 'like', "%{$keyword}%"))
                        ->orWhereHas('teachers', fn($teacherQuery) => $teacherQuery->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->addColumn('select', fn(Project $project): string => sprintf(
                '<input type="checkbox" class="project-row-check" value="%d">',
                $project->id
            ))
            ->addColumn('category', fn(Project $project): string => $project->category?->title ?? '-')
            ->addColumn('classes', fn(Project $project): string => $this->badges($project->grades->pluck('grade')->all()))
            ->addColumn('subjects', fn(Project $project): string => $this->badges($project->subjects->pluck('subject_name')->all()))
            ->addColumn('allocated_teachers', fn(Project $project): string => $this->badges($project->teachers->pluck('name')->all()))
            ->editColumn('duration_days', fn(Project $project): string => $project->duration_days . ' day(s)')
            ->editColumn('venue', fn(Project $project): string => e($project->venue ?: '-'))
            ->editColumn('created_at', fn(Project $project): string => $project->created_at?->format('d M Y') ?? '-')
            ->editColumn('timetable_replacement', fn(Project $project): string => sprintf(
                '<span class="%s">%s</span>',
                $project->timetable_replacement ? 'status-green' : 'status-red',
                $project->timetable_replacement ? 'Yes' : 'No'
            ))
            ->editColumn('status', fn(Project $project): string => sprintf(
                '<span class="%s">%s</span>',
                $this->statusClass($project->status),
                Project::STATUSES[$project->status] ?? ucfirst($project->status)
            ))
            ->addColumn('status_value', fn(Project $project): string => $project->status)
            ->addColumn('actions', fn(Project $project): string => $this->actionButtons($project))
            ->rawColumns(['select', 'classes', 'subjects', 'allocated_teachers', 'timetable_replacement', 'status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('projects.form', [
            'project' => new Project([
                'project_code' => $this->projectService->nextCode(),
                'status' => 'draft',
                'timetable_replacement' => false,
                'created_by_id' => auth()->id(),
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->projectService->create($request->validated());

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['category', 'grades', 'subjects', 'teachers', 'creator']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $project->load(['grades', 'subjects', 'teachers', 'creator']);

        return view('projects.form', [
            'project' => $project,
            ...$this->formOptions(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $this->projectService->delete($project);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Project deleted successfully.']);
        }

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function updateStatus(ProjectStatusRequest $request, Project $project): JsonResponse
    {
        $project = $this->projectService->updateStatus($project, $request->validated('status'));

        return response()->json([
            'message' => 'Project status updated successfully.',
            'status' => $project->status,
            'status_label' => Project::STATUSES[$project->status] ?? ucfirst($project->status),
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $projects = $this->selectedProjects($request);

        if ($projects->isEmpty()) {
            return back()->with('error', 'Select at least one project to export.');
        }

        return Excel::download(new ProjectsExport($projects), 'projects.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $projects = $this->selectedProjects($request);

        if ($projects->isEmpty()) {
            return back()->with('error', 'Select at least one project to export.');
        }

        return Pdf::loadView('projects.export-pdf', ['projects' => $projects])
            ->download('projects.pdf');
    }

    private function selectedProjects(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->projectService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'categories' => $this->projectService->categories(),
            'grades' => $this->projectService->grades(),
            'subjects' => $this->projectService->subjects(),
            'teachers' => $this->projectService->teachers(),
            'statuses' => Project::STATUSES,
        ];
    }

    private function actionButtons(Project $project): string
    {
        $buttons = '';

        $buttons .= sprintf(
            '<a href="%s" class="btn-edit" title="View"><i class="fa-solid fa-eye"></i></a>',
            route('projects.show', $project)
        );

        if (request()->user()?->can('edit.project')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('projects.edit', $project)
            );
        }

        if (request()->user()?->can('delete.project')) {
            $buttons .= view('projects.partials.delete-button', compact('project'))->render();
        }

        $menu = sprintf(
            '<div class="dropdown">
                <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    %s
                </ul>
            </div>',
            (request()->user()?->can('edit.project')
                ? sprintf(
                    '<li><button type="button" class="dropdown-item project-status-btn" data-project-id="%d" data-current-status="%s" data-status-url="%s">Change Status</button></li>',
                    $project->id,
                    e($project->status),
                    route('projects.update-status', $project)
                )
                : '')
                .sprintf('<li><a class="dropdown-item" href="%s">Project Schedule</a></li>', route('projects.schedules.index', $project))
        );

        return '<div class="action-btns">' . $buttons . $menu . '</div>';
    }

    private function statusClass(string $status): string
    {
        return match ($status) {
            'active', 'completed' => 'status-green',
            'cancelled' => 'status-red',
            'draft' => 'status-orange',
            default => '',
        };
    }

    private function badges(array $items): string
    {
        if (empty($items)) {
            return '-';
        }

        return collect($items)
            ->map(fn(string $item): string => '<span class="badge bg-light text-dark border me-1 mb-1">' . e($item) . '</span>')
            ->implode('');
    }
}
