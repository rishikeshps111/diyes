<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\District;
use App\Models\Grade;
use App\Models\State;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Teacher::query()
            ->with(['department', 'designation'])
            ->when($filters['department_id'] ?? null, fn (Builder $query, string $id) => $query->where('department_id', $id))
            ->when($filters['designation_id'] ?? null, fn (Builder $query, string $id) => $query->where('designation_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['gender'] ?? null, fn (Builder $query, string $gender) => $query->where('gender', $gender))
            ->when($filters['date_of_joining'] ?? null, fn (Builder $query, string $date) => $query->whereDate('date_of_joining', $date))
            ->when($filters['qualification'] ?? null, fn (Builder $query, string $qualification) => $query->where('qualification', 'like', "%{$qualification}%"));
    }

    public function selectedForExport(array $ids): Collection
    {
        return Teacher::query()
            ->with(['department', 'designation'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextEmployeeId(): string
    {
        return $this->prefixCodeService->next('teacher', Teacher::class, 'employee_id');
    }

    public function create(array $data): Teacher
    {
        $image = $data['teacher_image'] ?? null;
        unset($data['teacher_image']);

        return DB::transaction(function () use ($data, $image): Teacher {
            $teacher = Teacher::create([
                ...$this->teacherPayload($data),
                'employee_id' => $this->nextEmployeeId(),
                'teacher_image' => $image instanceof UploadedFile ? $image->store('teacher-images', 'public') : null,
                'is_verified' => false,
            ]);

            $this->syncSubjects($teacher, $data['subject_ids']);

            return $teacher;
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $image = $data['teacher_image'] ?? null;
        unset($data['teacher_image']);

        $payload = $this->teacherPayload($data);

        if ($image instanceof UploadedFile) {
            if ($teacher->teacher_image) {
                Storage::disk('public')->delete($teacher->teacher_image);
            }
            $payload['teacher_image'] = $image->store('teacher-images', 'public');
        }

        DB::transaction(function () use ($teacher, $payload, $data): void {
            $teacher->update($payload);
            $this->syncSubjects($teacher, $data['subject_ids']);
        });

        return $teacher;
    }

    public function delete(Teacher $teacher): void
    {
        if ($teacher->teacher_image) {
            Storage::disk('public')->delete($teacher->teacher_image);
        }
        $teacher->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $deleted = 0;

        Teacher::query()
            ->whereKey($ids)
            ->get()
            ->each(function (Teacher $teacher) use (&$deleted): void {
                $this->delete($teacher);
                $deleted++;
            });

        return $deleted;
    }

    public function createDocument(Teacher $teacher, array $data, UploadedFile $file): TeacherDocument
    {
        return $teacher->documents()->create([
            'document_type' => $data['document_type'],
            'document_file' => $file->store('teacher-documents', 'public'),
            'verification_status' => 'Pending',
        ]);
    }

    public function updateDocument(TeacherDocument $document, array $data, ?UploadedFile $file = null): TeacherDocument
    {
        $payload = ['document_type' => $data['document_type']];

        if ($file) {
            Storage::disk('public')->delete($document->document_file);
            $payload['document_file'] = $file->store('teacher-documents', 'public');
            $payload['verification_status'] = 'Pending';
            $payload['verified_by'] = null;
            $payload['verified_at'] = null;
        }

        $document->update($payload);
        $this->refreshTeacherVerification($document->teacher);

        return $document;
    }

    public function deleteDocument(TeacherDocument $document): void
    {
        $teacher = $document->teacher;
        Storage::disk('public')->delete($document->document_file);
        $document->delete();
        $this->refreshTeacherVerification($teacher);
    }

    public function verifyDocument(TeacherDocument $document, int $userId): TeacherDocument
    {
        $document->forceFill([
            'verification_status' => 'Verified',
            'verified_by' => $userId,
            'verified_at' => now(),
        ])->save();

        $this->refreshTeacherVerification($document->teacher);

        return $document;
    }

    public function verifyTeacher(Teacher $teacher): Teacher
    {
        $teacher->forceFill(['is_verified' => true])->save();

        return $teacher;
    }

    public function refreshTeacherVerification(Teacher $teacher): void
    {
        $hasDocuments = $teacher->documents()->exists();
        $hasPending = $teacher->documents()->where('verification_status', '!=', 'Verified')->exists();

        $teacher->forceFill(['is_verified' => $hasDocuments && ! $hasPending])->save();
    }

    public function departments(): Collection
    {
        return Department::query()->orderBy('department_name')->get(['id', 'department_name']);
    }

    public function designations(): Collection
    {
        return Designation::query()->orderBy('designation_name')->get(['id', 'designation_name']);
    }

    public function grades(): Collection
    {
        return Grade::query()->orderBy('grade')->get(['id', 'grade']);
    }

    public function subjects(): Collection
    {
        return Subject::query()
            ->active()
            ->with('grade')
            ->orderBy('subject_name')
            ->get(['id', 'subject_name', 'grade_id']);
    }

    public function countries(): Collection
    {
        return Country::query()->orderBy('name')->get(['id', 'name', 'phone_code']);
    }

    public function states(): Collection
    {
        return State::query()->orderBy('name')->get(['id', 'country_id', 'name']);
    }

    public function districts(): Collection
    {
        return District::query()->orderBy('name')->get(['id', 'state_id', 'name']);
    }

    private function teacherPayload(array $data): array
    {
        return Arr::only($data, [
            'name',
            'gender',
            'date_of_birth',
            'phone_country_code',
            'phone',
            'alternative_phone_country_code',
            'alternative_phone',
            'email',
            'qualification',
            'experience',
            'date_of_joining',
            'department_id',
            'subject',
            'is_class_in_charge',
            'country_id',
            'state_id',
            'district_id',
            'address',
            'pincode',
            'employment_type',
            'salary',
            'status',
        ]);
    }

    private function syncSubjects(Teacher $teacher, array $subjectIds): void
    {
        $subjects = Subject::query()->whereKey($subjectIds)->get(['id', 'subject_name', 'grade_id']);

        $teacher->forceFill(['subject' => $subjects->pluck('subject_name')->implode(', ')])->save();
        $teacher->subjectAssignments()->delete();
        $teacher->subjectAssignments()->createMany($subjects->map(fn (Subject $subject): array => [
            'subject_id' => $subject->id,
            'grade_id' => $subject->grade_id,
        ])->all());
    }
}
