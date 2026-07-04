<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Services\TeacherService;
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

class TeacherController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TeacherService $teacherService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.teacher', only: ['index', 'data', 'show', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.teacher', only: ['create', 'store']),
            new Middleware('can:edit.teacher', only: ['edit', 'update', 'verify']),
            new Middleware('can:delete.teacher', only: ['destroy', 'bulkDelete']),
        ];
    }

    public function index(): View
    {
        return view('teachers.index', [
            'departments' => $this->teacherService->departments(),
            'designations' => $this->teacherService->designations(),
            'genders' => Teacher::GENDERS,
            'statuses' => Teacher::STATUSES,
            'documentTypes' => TeacherDocument::DOCUMENT_TYPES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->teacherService->query($request->only([
            'department_id',
            'designation_id',
            'status',
            'gender',
            'date_of_joining',
            'qualification',
        ]));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request): void {
                $keyword = $request->input('search.value');

                if (! $keyword) {
                    return;
                }

                $query->where(function ($query) use ($keyword): void {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('employee_id', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('select', fn(Teacher $teacher): string => sprintf(
                '<input type="checkbox" class="teacher-row-check" value="%d">',
                $teacher->id
            ))
            ->addColumn('department', fn(Teacher $teacher): string => $teacher->department?->department_name ?? '-')
            ->addColumn('designation', fn(Teacher $teacher): string => $teacher->designation?->designation_name ?? '-')
            ->editColumn('phone', fn(Teacher $teacher): string => trim($teacher->phone_country_code . ' ' . $teacher->phone))
            ->editColumn('date_of_joining', fn(Teacher $teacher): string => $teacher->date_of_joining?->format('d M Y') ?? '-')
            ->editColumn('status', fn(Teacher $teacher): string => sprintf(
                '<span class="%s">%s</span>',
                $teacher->status === 'active' ? 'status-green' : 'status-red',
                ucfirst($teacher->status)
            ))
            ->addColumn('verification_status', fn(Teacher $teacher): string => sprintf(
                '<span class="%s">%s</span>',
                $teacher->is_verified ? 'status-green' : 'status-red',
                $teacher->is_verified ? 'Verified' : 'Pending'
            ))
            ->addColumn('actions', fn(Teacher $teacher): string => $this->actionButtons($teacher))
            ->rawColumns(['select', 'status', 'verification_status', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        $defaultCountry = $this->teacherService->countries()->first();

        return view('teachers.form', [
            'teacher' => new Teacher([
                'employee_id' => $this->teacherService->nextEmployeeId(),
                'phone_country_code' => '+91',
                'alternative_phone_country_code' => '+91',
                'country_id' => $defaultCountry?->id,
                'status' => 'active',
                'employment_type' => 'permanent',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $this->teacherService->create($request->validated());

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Request $request, Teacher $teacher): View|JsonResponse
    {
        $teacher->load(['department', 'designation', 'classInCharge', 'country', 'state', 'district', 'documents.verifier']);

        if (! $request->expectsJson()) {
            return view('teachers.show', compact('teacher'));
        }

        return response()->json([
            'employee_id' => $teacher->employee_id,
            'teacher_image' => $teacher->imageUrl(),
            'name' => $teacher->name,
            'gender' => $teacher->gender,
            'date_of_birth' => $teacher->date_of_birth?->format('d M Y'),
            'phone' => trim($teacher->phone_country_code . ' ' . $teacher->phone),
            'alternative_phone' => $teacher->alternative_phone ? trim($teacher->alternative_phone_country_code . ' ' . $teacher->alternative_phone) : '-',
            'email' => $teacher->email,
            'qualification' => $teacher->qualification,
            'experience' => $teacher->experience,
            'date_of_joining' => $teacher->date_of_joining?->format('d M Y'),
            'department' => $teacher->department?->department_name ?? '-',
            'designation' => $teacher->designation?->designation_name ?? '-',
            'subject' => $teacher->subject,
            'class_in_charge' => $teacher->classInCharge?->grade ?? '-',
            'country' => $teacher->country?->name ?? '-',
            'state' => $teacher->state?->name ?? '-',
            'district' => $teacher->district?->name ?? '-',
            'address' => $teacher->address,
            'pincode' => $teacher->pincode,
            'employment_type' => ucfirst($teacher->employment_type),
            'salary' => number_format((float) $teacher->salary, 2),
            'status' => ucfirst($teacher->status),
            'verification_status' => $teacher->is_verified ? 'Verified' : 'Pending',
        ]);
    }

    public function edit(Teacher $teacher): View
    {
        return view('teachers.form', [
            'teacher' => $teacher,
            ...$this->formOptions(),
        ]);
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->teacherService->update($teacher, $request->validated());

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Request $request, Teacher $teacher): JsonResponse|RedirectResponse
    {
        $this->teacherService->delete($teacher);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Teacher deleted successfully.']);
        }

        return redirect()
            ->route('teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }

    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Select at least one teacher to delete.',
                ], 422);
            }

            return back()->with('error', 'Select at least one teacher to delete.');
        }

        $deleted = $this->teacherService->bulkDelete($ids);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "{$deleted} teacher(s) deleted successfully.",
            ]);
        }

        return redirect()
            ->route('teachers.index')
            ->with('success', "{$deleted} teacher(s) deleted successfully.");
    }

    public function verify(Request $request, Teacher $teacher): JsonResponse|RedirectResponse
    {
        $this->teacherService->verifyTeacher($teacher);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Teacher verified successfully.']);
        }

        return back()->with('success', 'Teacher verified successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $teachers = $this->selectedTeachers($request);

        if ($teachers->isEmpty()) {
            return back()->with('error', 'Select at least one teacher to export.');
        }

        return Excel::download(new TeachersExport($teachers), 'teachers.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $teachers = $this->selectedTeachers($request);

        if ($teachers->isEmpty()) {
            return back()->with('error', 'Select at least one teacher to export.');
        }

        return Pdf::loadView('teachers.export-pdf', ['teachers' => $teachers])
            ->download('teachers.pdf');
    }

    private function selectedTeachers(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->teacherService->selectedForExport($ids);
    }

    private function formOptions(): array
    {
        return [
            'departments' => $this->teacherService->departments(),
            'designations' => $this->teacherService->designations(),
            'grades' => $this->teacherService->grades(),
            'countries' => $this->teacherService->countries(),
            'states' => $this->teacherService->states(),
            'districts' => $this->teacherService->districts(),
            'genders' => Teacher::GENDERS,
            'employmentTypes' => Teacher::EMPLOYMENT_TYPES,
            'statuses' => Teacher::STATUSES,
        ];
    }

    private function actionButtons(Teacher $teacher): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.teacher')) {
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('teachers.edit', $teacher)
            );
        }

        if (request()->user()?->can('delete.teacher')) {
            $buttons .= view('teachers.partials.delete-button', compact('teacher'))->render();
        }

        $menu = sprintf(
            '<div class="dropdown">
                <button class="dropdown-toggle tgle-cs-btns" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="%s">View Details</a></li>
                    %s
                    <li><a class="dropdown-item" href="%s">View Documents</a></li>
                </ul>
            </div>',
            route('teachers.show', $teacher),
            request()->user()?->can('edit.teacher')
                ? sprintf('<li><button type="button" class="dropdown-item teacher-verify-btn" data-verify-url="%s">Verify</button></li>', route('teachers.verify', $teacher))
                : '',
            route('teachers.documents.index', $teacher)
        );

        return '<div class="action-btns">' . $buttons . $menu . '</div>';
    }
}
