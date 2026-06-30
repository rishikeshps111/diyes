<?php

namespace App\Http\Controllers;

use App\Exports\HolidaysExport;
use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use App\Services\HolidayService;
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

class HolidayController extends Controller implements HasMiddleware
{
    public function __construct(private readonly HolidayService $holidayService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.holiday', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.holiday', only: ['create', 'store']),
            new Middleware('can:edit.holiday', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.holiday', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('holidays.index', [
            'academicYears' => $this->holidayService->academicYears(),
            'holidayTypes' => $this->holidayService->holidayTypes(),
            'months' => $this->holidayService->months(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->holidayService->query($request->only([
            'applicable_branch',
            'academic_year_id',
            'holiday_type',
            'month',
            'date_from',
            'date_to',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Holiday $holiday): string => sprintf(
                '<input type="checkbox" class="holiday-row-check" value="%d">',
                $holiday->id
            ))
            ->editColumn('holiday_date', fn (Holiday $holiday): string => $holiday->holiday_date?->format('d M Y') ?? '-')
            ->editColumn('applicable_branch', fn (Holiday $holiday): string => $holiday->applicable_branch ?? '-')
            ->editColumn('applicable_classes', fn (Holiday $holiday): string => $holiday->applicable_classes ?? '-')
            ->editColumn('is_active', fn (Holiday $holiday): string => sprintf(
                '<span class="%s">%s</span>',
                $holiday->is_active ? 'status-green' : 'status-red',
                $holiday->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Holiday $holiday): string => $this->actionButtons($holiday))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('holidays.form', [
            'holiday' => new Holiday([
                'code' => $this->holidayService->nextCode(),
                'is_active' => true,
            ]),
            'academicYears' => $this->holidayService->academicYears(),
            'holidayTypes' => $this->holidayService->holidayTypes(),
            'applicableClasses' => $this->holidayService->applicableClasses(),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $this->holidayService->create($request->validated());

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday): View
    {
        return view('holidays.form', [
            'holiday' => $holiday,
            'academicYears' => $this->holidayService->academicYears(),
            'holidayTypes' => $this->holidayService->holidayTypes(),
            'applicableClasses' => $this->holidayService->applicableClasses(),
        ]);
    }

    public function update(HolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $this->holidayService->update($holiday, $request->validated());

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Request $request, Holiday $holiday): JsonResponse|RedirectResponse
    {
        $this->holidayService->delete($holiday);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Holiday deleted successfully.',
            ]);
        }

        return redirect()
            ->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    public function toggleStatus(Request $request, Holiday $holiday): JsonResponse|RedirectResponse
    {
        $holiday = $this->holidayService->toggleStatus($holiday);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Holiday status updated successfully.',
                'is_active' => $holiday->is_active,
                'status' => $holiday->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Holiday status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $holidays = $this->selectedHolidays($request);

        if ($holidays->isEmpty()) {
            return back()->with('error', 'Select at least one holiday to export.');
        }

        return Excel::download(new HolidaysExport($holidays), 'holidays.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $holidays = $this->selectedHolidays($request);

        if ($holidays->isEmpty()) {
            return back()->with('error', 'Select at least one holiday to export.');
        }

        return Pdf::loadView('holidays.export-pdf', ['holidays' => $holidays])
            ->download('holidays.pdf');
    }

    private function selectedHolidays(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->holidayService->selectedForExport($ids);
    }

    private function actionButtons(Holiday $holiday): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.holiday')) {
            $buttons .= view('holidays.partials.toggle-status', compact('holiday'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('holidays.edit', $holiday)
            );
        }

        if (request()->user()?->can('delete.holiday')) {
            $buttons .= view('holidays.partials.delete-button', compact('holiday'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
