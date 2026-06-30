<?php

namespace App\Http\Controllers;

use App\Exports\VenuesExport;
use App\Http\Requests\VenueRequest;
use App\Models\Venue;
use App\Services\VenueService;
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

class VenueController extends Controller implements HasMiddleware
{
    public function __construct(private readonly VenueService $venueService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.venue', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.venue', only: ['create', 'store']),
            new Middleware('can:edit.venue', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.venue', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('venues.index', [
            'venueTypes' => $this->venueService->venueTypes(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->venueService->query($request->only([
            'building',
            'venue_type',
            'capacity',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Venue $venue): string => sprintf(
                '<input type="checkbox" class="venue-row-check" value="%d">',
                $venue->id
            ))
            ->editColumn('is_active', fn (Venue $venue): string => sprintf(
                '<span class="%s">%s</span>',
                $venue->is_active ? 'status-green' : 'status-red',
                $venue->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Venue $venue): string => $this->actionButtons($venue))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('venues.form', [
            'venue' => new Venue([
                'code' => $this->venueService->nextCode(),
                'is_active' => true,
            ]),
            'venueTypes' => $this->venueService->venueTypes(),
            'facilityOptions' => $this->venueService->facilityOptions(),
        ]);
    }

    public function store(VenueRequest $request): RedirectResponse
    {
        $this->venueService->create($request->validated());

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue created successfully.');
    }

    public function edit(Venue $venue): View
    {
        return view('venues.form', [
            'venue' => $venue,
            'venueTypes' => $this->venueService->venueTypes(),
            'facilityOptions' => $this->venueService->facilityOptions(),
        ]);
    }

    public function update(VenueRequest $request, Venue $venue): RedirectResponse
    {
        $this->venueService->update($venue, $request->validated());

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue updated successfully.');
    }

    public function destroy(Request $request, Venue $venue): JsonResponse|RedirectResponse
    {
        $this->venueService->delete($venue);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Venue deleted successfully.',
            ]);
        }

        return redirect()
            ->route('venues.index')
            ->with('success', 'Venue deleted successfully.');
    }

    public function toggleStatus(Request $request, Venue $venue): JsonResponse|RedirectResponse
    {
        $venue = $this->venueService->toggleStatus($venue);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Venue status updated successfully.',
                'is_active' => $venue->is_active,
                'status' => $venue->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Venue status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $venues = $this->selectedVenues($request);

        if ($venues->isEmpty()) {
            return back()->with('error', 'Select at least one venue to export.');
        }

        return Excel::download(new VenuesExport($venues), 'venues.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $venues = $this->selectedVenues($request);

        if ($venues->isEmpty()) {
            return back()->with('error', 'Select at least one venue to export.');
        }

        return Pdf::loadView('venues.export-pdf', ['venues' => $venues])
            ->download('venues.pdf');
    }

    private function selectedVenues(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->venueService->selectedForExport($ids);
    }

    private function actionButtons(Venue $venue): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.venue')) {
            $buttons .= view('venues.partials.toggle-status', compact('venue'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('venues.edit', $venue)
            );
        }

        if (request()->user()?->can('delete.venue')) {
            $buttons .= view('venues.partials.delete-button', compact('venue'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
