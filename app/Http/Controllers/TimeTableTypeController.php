<?php

namespace App\Http\Controllers;

use App\Exports\TimeTableTypesExport;
use App\Http\Requests\TimeTableTypeRequest;
use App\Models\TimeTableType;
use App\Services\TimeTableTypeService;
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

class TimeTableTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TimeTableTypeService $timeTableTypeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.time-table-type', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.time-table-type', only: ['create', 'store']),
            new Middleware('can:edit.time-table-type', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.time-table-type', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('time-table-types.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->timeTableTypeService->query($request->only([
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
            ->addColumn('select', fn (TimeTableType $timeTableType): string => sprintf(
                '<input type="checkbox" class="time-table-type-row-check" value="%d">',
                $timeTableType->id
            ))
            ->editColumn('is_active', fn (TimeTableType $timeTableType): string => sprintf(
                '<span class="%s">%s</span>',
                $timeTableType->is_active ? 'status-green' : 'status-red',
                $timeTableType->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (TimeTableType $timeTableType): string => $this->actionButtons($timeTableType))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('time-table-types.form', [
            'timeTableType' => new TimeTableType([
                'code' => $this->timeTableTypeService->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(TimeTableTypeRequest $request): RedirectResponse
    {
        $this->timeTableTypeService->create($request->validated());

        return redirect()
            ->route('time-table-types.index')
            ->with('success', 'Time table type created successfully.');
    }

    public function edit(TimeTableType $timeTableType): View
    {
        return view('time-table-types.form', [
            'timeTableType' => $timeTableType,
        ]);
    }

    public function update(TimeTableTypeRequest $request, TimeTableType $timeTableType): RedirectResponse
    {
        $this->timeTableTypeService->update($timeTableType, $request->validated());

        return redirect()
            ->route('time-table-types.index')
            ->with('success', 'Time table type updated successfully.');
    }

    public function destroy(Request $request, TimeTableType $timeTableType): JsonResponse|RedirectResponse
    {
        $this->timeTableTypeService->delete($timeTableType);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Time table type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('time-table-types.index')
            ->with('success', 'Time table type deleted successfully.');
    }

    public function toggleStatus(Request $request, TimeTableType $timeTableType): JsonResponse|RedirectResponse
    {
        $timeTableType = $this->timeTableTypeService->toggleStatus($timeTableType);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Time table type status updated successfully.',
                'is_active' => $timeTableType->is_active,
                'status' => $timeTableType->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Time table type status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $timeTableTypes = $this->selectedTimeTableTypes($request);

        if ($timeTableTypes->isEmpty()) {
            return back()->with('error', 'Select at least one time table type to export.');
        }

        return Excel::download(new TimeTableTypesExport($timeTableTypes), 'time-table-types.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $timeTableTypes = $this->selectedTimeTableTypes($request);

        if ($timeTableTypes->isEmpty()) {
            return back()->with('error', 'Select at least one time table type to export.');
        }

        return Pdf::loadView('time-table-types.export-pdf', ['timeTableTypes' => $timeTableTypes])
            ->download('time-table-types.pdf');
    }

    private function selectedTimeTableTypes(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->timeTableTypeService->selectedForExport($ids);
    }

    private function actionButtons(TimeTableType $timeTableType): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.time-table-type')) {
            $buttons .= view('time-table-types.partials.toggle-status', compact('timeTableType'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('time-table-types.edit', $timeTableType)
            );
        }

        if (request()->user()?->can('delete.time-table-type')) {
            $buttons .= view('time-table-types.partials.delete-button', compact('timeTableType'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
