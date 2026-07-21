<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherSubjectRequest;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherSubjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view.teacher', only: ['index', 'data', 'show']),
            new Middleware('can:edit.teacher', only: ['store', 'update', 'destroy']),
        ];
    }

    public function index(Teacher $teacher): View
    {
        return view('teachers.subjects.index', [
            'teacher' => $teacher,
            'grades' => Grade::query()->orderBy('grade')->get(),
            'subjects' => Subject::query()->with('grade')->orderBy('subject_name')->get(),
        ]);
    }

    public function data(Teacher $teacher): JsonResponse
    {
        return DataTables::eloquent(
            TeacherSubject::query()->with(['grade', 'subject'])->where('teacher_id', $teacher->id)
        )
            ->addIndexColumn()
            ->addColumn('grade', fn (TeacherSubject $assignment): string => $assignment->grade?->grade ?? '-')
            ->addColumn('subject_name', fn (TeacherSubject $assignment): string => $assignment->subject?->subject_name ?? '-')
            ->addColumn('actions', fn (TeacherSubject $assignment): string => $this->actionButtons($teacher, $assignment))
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function show(Teacher $teacher, TeacherSubject $teacherSubject): JsonResponse
    {
        $this->ensureBelongsToTeacher($teacher, $teacherSubject);

        return response()->json([
            'id' => $teacherSubject->id,
            'grade_id' => $teacherSubject->grade_id,
            'subject_id' => $teacherSubject->subject_id,
        ]);
    }

    public function store(TeacherSubjectRequest $request, Teacher $teacher): JsonResponse
    {
        $assignment = $teacher->subjectAssignments()->create($request->validated());

        return response()->json(['message' => 'Subject assigned successfully.', 'assignment' => $assignment]);
    }

    public function update(TeacherSubjectRequest $request, Teacher $teacher, TeacherSubject $teacherSubject): JsonResponse
    {
        $this->ensureBelongsToTeacher($teacher, $teacherSubject);
        $teacherSubject->update($request->validated());

        return response()->json(['message' => 'Subject assignment updated successfully.', 'assignment' => $teacherSubject]);
    }

    public function destroy(Teacher $teacher, TeacherSubject $teacherSubject): JsonResponse
    {
        $this->ensureBelongsToTeacher($teacher, $teacherSubject);
        $teacherSubject->delete();

        return response()->json(['message' => 'Subject assignment deleted successfully.']);
    }

    private function ensureBelongsToTeacher(Teacher $teacher, TeacherSubject $teacherSubject): void
    {
        abort_unless($teacherSubject->teacher_id === $teacher->id, 404);
    }

    private function actionButtons(Teacher $teacher, TeacherSubject $assignment): string
    {
        if (! request()->user()?->can('edit.teacher')) {
            return '-';
        }

        return sprintf(
            '<div class="action-btns"><button type="button" class="btn-edit teacher-subject-edit-btn" data-view-url="%s" data-update-url="%s" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button><button type="button" class="btn-delete teacher-subject-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button></div>',
            route('teachers.subjects.show', [$teacher, $assignment]),
            route('teachers.subjects.update', [$teacher, $assignment]),
            route('teachers.subjects.destroy', [$teacher, $assignment])
        );
    }
}
