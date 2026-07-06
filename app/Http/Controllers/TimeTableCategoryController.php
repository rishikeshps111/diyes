<?php

namespace App\Http\Controllers;

use App\Exports\TimeTableCategoriesExport;
use App\Http\Requests\TimeTableCategoryRequest;
use App\Models\TimeTableCategory;
use App\Services\TimeTableCategoryService;
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

class TimeTableCategoryController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TimeTableCategoryService $timeTableCategoryService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.time-table-category', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.time-table-category', only: ['create', 'store']),
            new Middleware('can:edit.time-table-category', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.time-table-category', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('time-table-categories.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->timeTableCategoryService->query($request->only([
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
            ->addColumn('select', fn (TimeTableCategory $timeTableCategory): string => sprintf(
                '<input type="checkbox" class="time-table-category-row-check" value="%d">',
                $timeTableCategory->id
            ))
            ->editColumn('is_active', fn (TimeTableCategory $timeTableCategory): string => sprintf(
                '<span class="%s">%s</span>',
                $timeTableCategory->is_active ? 'status-green' : 'status-red',
                $timeTableCategory->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (TimeTableCategory $timeTableCategory): string => $this->actionButtons($timeTableCategory))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('time-table-categories.form', [
            'timeTableCategory' => new TimeTableCategory([
                'code' => $this->timeTableCategoryService->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(TimeTableCategoryRequest $request): RedirectResponse
    {
        $this->timeTableCategoryService->create($request->validated());

        return redirect()
            ->route('time-table-categories.index')
            ->with('success', 'Time table category created successfully.');
    }

    public function edit(TimeTableCategory $timeTableCategory): View
    {
        return view('time-table-categories.form', [
            'timeTableCategory' => $timeTableCategory,
        ]);
    }

    public function update(TimeTableCategoryRequest $request, TimeTableCategory $timeTableCategory): RedirectResponse
    {
        $this->timeTableCategoryService->update($timeTableCategory, $request->validated());

        return redirect()
            ->route('time-table-categories.index')
            ->with('success', 'Time table category updated successfully.');
    }

    public function destroy(Request $request, TimeTableCategory $timeTableCategory): JsonResponse|RedirectResponse
    {
        $this->timeTableCategoryService->delete($timeTableCategory);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Time table category deleted successfully.',
            ]);
        }

        return redirect()
            ->route('time-table-categories.index')
            ->with('success', 'Time table category deleted successfully.');
    }

    public function toggleStatus(Request $request, TimeTableCategory $timeTableCategory): JsonResponse|RedirectResponse
    {
        $timeTableCategory = $this->timeTableCategoryService->toggleStatus($timeTableCategory);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Time table category status updated successfully.',
                'is_active' => $timeTableCategory->is_active,
                'status' => $timeTableCategory->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Time table category status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $timeTableCategories = $this->selectedTimeTableCategories($request);

        if ($timeTableCategories->isEmpty()) {
            return back()->with('error', 'Select at least one time table category to export.');
        }

        return Excel::download(new TimeTableCategoriesExport($timeTableCategories), 'time-table-categories.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $timeTableCategories = $this->selectedTimeTableCategories($request);

        if ($timeTableCategories->isEmpty()) {
            return back()->with('error', 'Select at least one time table category to export.');
        }

        return Pdf::loadView('time-table-categories.export-pdf', ['timeTableCategories' => $timeTableCategories])
            ->download('time-table-categories.pdf');
    }

    private function selectedTimeTableCategories(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->timeTableCategoryService->selectedForExport($ids);
    }

    private function actionButtons(TimeTableCategory $timeTableCategory): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.time-table-category')) {
            $buttons .= view('time-table-categories.partials.toggle-status', compact('timeTableCategory'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('time-table-categories.edit', $timeTableCategory)
            );
        }

        if (request()->user()?->can('delete.time-table-category')) {
            $buttons .= view('time-table-categories.partials.delete-button', compact('timeTableCategory'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
