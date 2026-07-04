<?php

namespace App\Http\Controllers;

use App\Exports\SubjectsExport;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use App\Services\SubjectService;
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

class SubjectController extends Controller implements HasMiddleware
{
    public function __construct(private readonly SubjectService $subjectService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.subject', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.subject', only: ['create', 'store']),
            new Middleware('can:edit.subject', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.subject', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('subjects.index', [
            'grades' => $this->subjectService->grades(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = $this->subjectService->query($request->only([
            'grade_id',
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
                    $query->where('subject_code', 'like', "%{$keyword}%")
                        ->orWhere('subject_name', 'like', "%{$keyword}%")
                        ->orWhereHas('grade', function ($query) use ($keyword): void {
                            $query->where('grade', 'like', "%{$keyword}%")
                                ->orWhereHas('academicYear', function ($query) use ($keyword): void {
                                    $query->where('academic_year', 'like', "%{$keyword}%");
                                });
                        });
                });
            })
            ->addColumn('select', fn (Subject $subject): string => sprintf(
                '<input type="checkbox" class="subject-row-check" value="%d">',
                $subject->id
            ))
            ->addColumn('grade', fn (Subject $subject): string => $this->gradeWithYear($subject))
            ->editColumn('is_active', fn (Subject $subject): string => sprintf(
                '<span class="%s">%s</span>',
                $subject->is_active ? 'status-green' : 'status-red',
                $subject->is_active ? 'Active' : 'Inactive'
            ))
            ->addColumn('actions', fn (Subject $subject): string => $this->actionButtons($subject))
            ->filterColumn('grade', function ($query, string $keyword): void {
                $query->whereHas('grade', function ($query) use ($keyword): void {
                    $query->where('grade', 'like', "%{$keyword}%")
                        ->orWhereHas('academicYear', function ($query) use ($keyword): void {
                            $query->where('academic_year', 'like', "%{$keyword}%");
                        });
                });
            })
            ->rawColumns(['select', 'is_active', 'actions'])
            ->toJson();
    }

    public function create(): View
    {
        return view('subjects.form', [
            'subject' => new Subject([
                'subject_code' => $this->subjectService->nextCode(),
                'is_active' => true,
                'priority' => 'medium',
                'is_praticals' => false,
            ]),
            'grades' => $this->subjectService->grades(),
            'priorities' => Subject::PRIORITIES,
        ]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $this->subjectService->create($request->validated());

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.form', [
            'subject' => $subject,
            'grades' => $this->subjectService->grades(),
            'priorities' => Subject::PRIORITIES,
        ]);
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->subjectService->update($subject, $request->validated());

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Request $request, Subject $subject): JsonResponse|RedirectResponse
    {
        $this->subjectService->delete($subject);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subject deleted successfully.',
            ]);
        }

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    public function toggleStatus(Request $request, Subject $subject): JsonResponse|RedirectResponse
    {
        $subject = $this->subjectService->toggleStatus($subject);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subject status updated successfully.',
                'is_active' => $subject->is_active,
                'status' => $subject->is_active ? 'Active' : 'Inactive',
            ]);
        }

        return back()->with('success', 'Subject status updated successfully.');
    }

    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $subjects = $this->selectedSubjects($request);

        if ($subjects->isEmpty()) {
            return back()->with('error', 'Select at least one subject to export.');
        }

        return Excel::download(new SubjectsExport($subjects), 'subjects.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $subjects = $this->selectedSubjects($request);

        if ($subjects->isEmpty()) {
            return back()->with('error', 'Select at least one subject to export.');
        }

        return Pdf::loadView('subjects.export-pdf', ['subjects' => $subjects])
            ->download('subjects.pdf');
    }

    private function selectedSubjects(Request $request)
    {
        $ids = collect($request->input('selected_ids', []))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $this->subjectService->selectedForExport($ids);
    }

    private function actionButtons(Subject $subject): string
    {
        $buttons = '';

        if (request()->user()?->can('edit.subject')) {
            $buttons .= view('subjects.partials.toggle-status', compact('subject'))->render();
            $buttons .= sprintf(
                '<a href="%s" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i></a>',
                route('subjects.edit', $subject)
            );
        }

        if (request()->user()?->can('delete.subject')) {
            $buttons .= view('subjects.partials.delete-button', compact('subject'))->render();
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }

    private function gradeWithYear(Subject $subject): string
    {
        if (! $subject->grade) {
            return '-';
        }

        $academicYear = $subject->grade->academicYear?->academic_year;

        return $academicYear
            ? $subject->grade->grade.' - '.$academicYear
            : $subject->grade->grade;
    }
}
