<?php

namespace App\Http\Controllers;

use App\Exports\ClassroomsExport;
use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;
use App\Services\ClassroomService;
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

class ClassroomController extends Controller implements HasMiddleware
{
    public function __construct(private readonly ClassroomService $classroomService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.classroom', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.classroom', only: ['create', 'store']),
            new Middleware('can:edit.classroom', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.classroom', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('classrooms.index', [
            'departments' => $this->classroomService->departments(),
            'roomTypes' => $this->classroomService->roomTypes(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->classroomService->query($request->only([
            'building',
            'floor',
            'room_type',
            'department_id',
            'seating_capacity',
            'is_active',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('select', fn (Classroom $classroom): string => sprintf(
                '<input type="checkbox" class="classroom-row-check" value="%d">',
                $classroom->id
            ))
            ->editColumn('is_active', fn (Classroom $classroom): string => sprintf(
                '<span class="%s">%s</span>',
                $classroom->is_active ? 'status-green' : 'status-red',
                $classroom->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Classroom $classroom): string => $this->actionButtons($classroom))
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('classrooms.form', [
            'classroom' => new Classroom([
                'code' => $this->classroomService->nextCode(),
                'is_active' => true,
            ]),
            'departments' => $this->classroomService->departments(),
            'roomTypes' => $this->classroomService->roomTypes(),
            'equipmentOptions' => $this->classroomService->equipmentOptions(),
        ]);
    }

    public function store(ClassroomRequest $request): RedirectResponse
    {
        $this->classroomService->create($request->validated());

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom created successfully.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('classrooms.form', [
            'classroom' => $classroom,
            'departments' => $this->classroomService->departments(),
            'roomTypes' => $this->classroomService->roomTypes(),
            'equipmentOptions' => $this->classroomService->equipmentOptions(),
        ]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $this->classroomService->update($classroom, $request->validated());

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom updated successfully.');
    }

    public function destroy(Request $request, Classroom $classroom): JsonResponse|RedirectResponse
    {
        $this->classroomService->delete($classroom);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Classroom deleted successfully.',
            ]);
        }

        return redirect()
            ->route('classrooms.index')
            ->with('success', 'Classroom deleted successfully.');
    }

    public function toggleStatus(Request $request, Classroom $classroom): JsonResponse|RedirectResponse
    {
        $classroom = $this->classroomService->toggleStatus($classroom);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Classroom status updated successfully.',
                'is_active' => $classroom->is_active,
                'status' => $classroom->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Classroom status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $classrooms = $this->selectedClassrooms($request);

        if ($classrooms->isEmpty()) {
            return back()->with('error', 'Select at least one classroom to export.');
        }

        return Excel::download(new ClassroomsExport($classrooms), 'classrooms.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $classrooms = $this->selectedClassrooms($request);

        if ($classrooms->isEmpty()) {
            return back()->with('error', 'Select at least one classroom to export.');
        }

        return Pdf::loadView('classrooms.export-pdf', ['classrooms' => $classrooms])
            ->download('classrooms.pdf');
    }

    private function selectedClassrooms(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->classroomService->selectedForExport($ids);
    }

    private function actionButtons(Classroom $classroom): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.classroom')) {
            $buttons .= view('classrooms.partials.toggle-status', compact('classroom'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('classrooms.edit', $classroom)
            );
        }

        if (request()->user()?->can('delete.classroom')) {
            $buttons .= view('classrooms.partials.delete-button', compact('classroom'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
