<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherDocumentRequest;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TeacherDocumentController extends Controller implements HasMiddleware
{
    public function __construct(private readonly TeacherService $teacherService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.teacher', only: ['index', 'data', 'show']),
            new Middleware('can:edit.teacher', only: ['store', 'update', 'destroy', 'verify']),
        ];
    }

    public function index(Teacher $teacher): View
    {
        return view('teachers.documents.index', [
            'teacher' => $teacher,
            'documentTypes' => TeacherDocument::DOCUMENT_TYPES,
        ]);
    }

    public function data(Teacher $teacher): JsonResponse
    {
        return DataTables::eloquent(
            TeacherDocument::query()
                ->with('verifier')
                ->where('teacher_id', $teacher->id)
        )
            ->addIndexColumn()
            ->editColumn('verification_status', fn (TeacherDocument $document): string => sprintf(
                '<span class="%s">%s</span>',
                $document->verification_status === 'Verified' ? 'status-green' : 'status-red',
                $document->verification_status
            ))
            ->addColumn('verified_by_name', fn (TeacherDocument $document): string => $document->verifier?->name ?? '-')
            ->editColumn('verified_at', fn (TeacherDocument $document): string => $document->verified_at?->format('d M Y h:i A') ?? '-')
            ->addColumn('actions', fn (TeacherDocument $document): string => $this->actionButtons($teacher, $document))
            ->rawColumns(['verification_status', 'actions'])
            ->toJson();
    }

    public function show(Request $request, Teacher $teacher, TeacherDocument $document): View|JsonResponse
    {
        $this->ensureDocumentBelongsToTeacher($teacher, $document);

        if (! $request->expectsJson()) {
            return view('teachers.documents.show', compact('teacher', 'document'));
        }

        return response()->json([
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_file_url' => $document->fileUrl(),
            'file_name' => basename($document->document_file),
            'verification_status' => $document->verification_status,
        ]);
    }

    public function store(TeacherDocumentRequest $request, Teacher $teacher): JsonResponse
    {
        $document = $this->teacherService->createDocument(
            $teacher,
            $request->validated(),
            $request->file('document_file')
        );

        return response()->json([
            'message' => 'Teacher document uploaded successfully.',
            'document' => $document,
        ]);
    }

    public function update(TeacherDocumentRequest $request, Teacher $teacher, TeacherDocument $document): JsonResponse
    {
        $this->ensureDocumentBelongsToTeacher($teacher, $document);

        $document = $this->teacherService->updateDocument(
            $document,
            $request->validated(),
            $request->file('document_file')
        );

        return response()->json([
            'message' => 'Teacher document updated successfully.',
            'document' => $document,
        ]);
    }

    public function destroy(Teacher $teacher, TeacherDocument $document): JsonResponse
    {
        $this->ensureDocumentBelongsToTeacher($teacher, $document);
        $this->teacherService->deleteDocument($document);

        return response()->json(['message' => 'Teacher document deleted successfully.']);
    }

    public function verify(Request $request, Teacher $teacher, TeacherDocument $document): JsonResponse
    {
        $this->ensureDocumentBelongsToTeacher($teacher, $document);
        $this->teacherService->verifyDocument($document, (int) $request->user()->id);

        return response()->json(['message' => 'Teacher document verified successfully.']);
    }

    private function ensureDocumentBelongsToTeacher(Teacher $teacher, TeacherDocument $document): void
    {
        abort_unless($document->teacher_id === $teacher->id, 404);
    }

    private function actionButtons(Teacher $teacher, TeacherDocument $document): string
    {
        $viewUrl = route('teachers.documents.show', [$teacher, $document]);
        $buttons = sprintf(
            '<button type="button" class="btn-edit teacher-document-view-btn" data-view-url="%s" title="View Document"><i class="fa-solid fa-eye"></i></button>',
            $viewUrl
        );

        if (request()->user()?->can('edit.teacher')) {
            $buttons .= sprintf(
                '<button type="button" class="btn-edit teacher-document-edit-btn" data-view-url="%s" data-update-url="%s" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>',
                $viewUrl,
                route('teachers.documents.update', [$teacher, $document])
            );
            $buttons .= sprintf(
                '<button type="button" class="btn-delete teacher-document-delete-btn" data-delete-url="%s" title="Delete"><i class="fa-solid fa-trash"></i></button>',
                route('teachers.documents.destroy', [$teacher, $document])
            );
            $buttons .= sprintf(
                '<button type="button" class="btn-edit teacher-document-verify-btn" data-verify-url="%s" title="Verify"><i class="fa-solid fa-circle-check"></i></button>',
                route('teachers.documents.verify', [$teacher, $document])
            );
        }

        return '<div class="action-btns">'.$buttons.'</div>';
    }
}
