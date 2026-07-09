<?php

namespace App\Http\Controllers;

use App\Exports\ProjectCategoriesExport;
use App\Http\Requests\ProjectCategoryRequest;
use App\Models\ProjectCategory;
use App\Services\ProjectCategoryService;
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

class ProjectCategoryController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ProjectCategoryService $projectCategoryService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.project-category', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.project-category', only: ['create', 'store']),
            new Middleware('can:edit.project-category', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.project-category', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('project-categories.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->projectCategoryService->query($request->only([
            'is_active',
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
                        ->orWhere('title', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('select', fn (ProjectCategory $projectCategory): string => sprintf(
                '<input type="checkbox" class="project-category-row-check" value="%d">',
                $projectCategory->id
            ))
            ->editColumn('is_active', fn (ProjectCategory $projectCategory): string => sprintf(
                '<span class="%s">%s</span>',
                $projectCategory->is_active ? 'status-green' : 'status-red',
                $projectCategory->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (ProjectCategory $projectCategory): string => $this->actionButtons($projectCategory))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('project-categories.form', [
            'projectCategory' => new ProjectCategory([
                'code' => $this->projectCategoryService->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(ProjectCategoryRequest $request): RedirectResponse
    {
        $this->projectCategoryService->create($request->validated());

        return redirect()
            ->route('project-categories.index')
            ->with('success', 'Project category created successfully.');
    }

    public function edit(ProjectCategory $projectCategory): View
    {
        return view('project-categories.form', [
            'projectCategory' => $projectCategory,
        ]);
    }

    public function update(ProjectCategoryRequest $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $this->projectCategoryService->update($projectCategory, $request->validated());

        return redirect()
            ->route('project-categories.index')
            ->with('success', 'Project category updated successfully.');
    }

    public function destroy(Request $request, ProjectCategory $projectCategory): JsonResponse|RedirectResponse
    {
        $this->projectCategoryService->delete($projectCategory);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Project category deleted successfully.',
            ]);
        }

        return redirect()
            ->route('project-categories.index')
            ->with('success', 'Project category deleted successfully.');
    }

    public function toggleStatus(Request $request, ProjectCategory $projectCategory): JsonResponse|RedirectResponse
    {
        $projectCategory = $this->projectCategoryService->toggleStatus($projectCategory);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Project category status updated successfully.',
                'is_active' => $projectCategory->is_active,
                'status' => $projectCategory->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Project category status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $projectCategories = $this->selectedProjectCategories($request);

        if ($projectCategories->isEmpty()) {
            return back()->with('error', 'Select at least one project category to export.');
        }

        return Excel::download(new ProjectCategoriesExport($projectCategories), 'project-categories.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $projectCategories = $this->selectedProjectCategories($request);

        if ($projectCategories->isEmpty()) {
            return back()->with('error', 'Select at least one project category to export.');
        }

        return Pdf::loadView('project-categories.export-pdf', ['projectCategories' => $projectCategories])
            ->download('project-categories.pdf');
    }

    private function selectedProjectCategories(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->projectCategoryService->selectedForExport($ids);
    }

    private function actionButtons(ProjectCategory $projectCategory): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.project-category')) {
            $buttons .= view('project-categories.partials.toggle-status', compact('projectCategory'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('project-categories.edit', $projectCategory)
            );
        }

        if (request()->user()?->can('delete.project-category')) {
            $buttons .= view('project-categories.partials.delete-button', compact('projectCategory'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
