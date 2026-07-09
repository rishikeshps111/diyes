<?php

namespace App\Http\Controllers;

use App\Exports\EventTypesExport;
use App\Http\Requests\EventTypeRequest;
use App\Models\EventType;
use App\Services\EventTypeService;
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

class EventTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly EventTypeService $eventTypeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.event-type', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.event-type', only: ['create', 'store']),
            new Middleware('can:edit.event-type', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.event-type', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('event-types.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->eventTypeService->query($request->only([
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
            ->addColumn('select', fn (EventType $eventType): string => sprintf(
                '<input type="checkbox" class="event-type-row-check" value="%d">',
                $eventType->id
            ))
            ->editColumn('is_active', fn (EventType $eventType): string => sprintf(
                '<span class="%s">%s</span>',
                $eventType->is_active ? 'status-green' : 'status-red',
                $eventType->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (EventType $eventType): string => $this->actionButtons($eventType))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('event-types.form', [
            'eventType' => new EventType([
                'code' => $this->eventTypeService->nextCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(EventTypeRequest $request): RedirectResponse
    {
        $this->eventTypeService->create($request->validated());

        return redirect()
            ->route('event-types.index')
            ->with('success', 'Event type created successfully.');
    }

    public function edit(EventType $eventType): View
    {
        return view('event-types.form', [
            'eventType' => $eventType,
        ]);
    }

    public function update(EventTypeRequest $request, EventType $eventType): RedirectResponse
    {
        $this->eventTypeService->update($eventType, $request->validated());

        return redirect()
            ->route('event-types.index')
            ->with('success', 'Event type updated successfully.');
    }

    public function destroy(Request $request, EventType $eventType): JsonResponse|RedirectResponse
    {
        $this->eventTypeService->delete($eventType);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Event type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('event-types.index')
            ->with('success', 'Event type deleted successfully.');
    }

    public function toggleStatus(Request $request, EventType $eventType): JsonResponse|RedirectResponse
    {
        $eventType = $this->eventTypeService->toggleStatus($eventType);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Event type status updated successfully.',
                'is_active' => $eventType->is_active,
                'status' => $eventType->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Event type status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $eventTypes = $this->selectedEventTypes($request);

        if ($eventTypes->isEmpty()) {
            return back()->with('error', 'Select at least one event type to export.');
        }

        return Excel::download(new EventTypesExport($eventTypes), 'event-types.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $eventTypes = $this->selectedEventTypes($request);

        if ($eventTypes->isEmpty()) {
            return back()->with('error', 'Select at least one event type to export.');
        }

        return Pdf::loadView('event-types.export-pdf', ['eventTypes' => $eventTypes])
            ->download('event-types.pdf');
    }

    private function selectedEventTypes(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->eventTypeService->selectedForExport($ids);
    }

    private function actionButtons(EventType $eventType): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.event-type')) {
            $buttons .= view('event-types.partials.toggle-status', compact('eventType'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('event-types.edit', $eventType)
            );
        }

        if (request()->user()?->can('delete.event-type')) {
            $buttons .= view('event-types.partials.delete-button', compact('eventType'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
