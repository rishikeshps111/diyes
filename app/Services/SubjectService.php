<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class SubjectService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Subject::query()
            ->with(['grade.academicYear'])
            ->when($filters['grade_id'] ?? null, function (Builder $query, string $gradeId): void {
                $query->where('grade_id', $gradeId);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Subject::query()
            ->with(['grade.academicYear'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function grades(): Collection
    {
        return Grade::query()
            ->with('academicYear')
            ->orderBy('grade')
            ->get(['id', 'grade', 'academic_year_id']);
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('subject', Subject::class, 'subject_code');
    }

    public function create(array $data): Subject
    {
        return Subject::create([
            ...Arr::only($data, [
                'subject_name',
                'grade_id',
                'color',
                'is_active',
                'priority',
                'is_praticals',
            ]),
            'subject_code' => $this->nextCode(),
        ]);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update(Arr::only($data, [
            'subject_name',
            'grade_id',
            'color',
            'is_active',
            'priority',
            'is_praticals',
        ]));

        return $subject;
    }

    public function toggleStatus(Subject $subject): Subject
    {
        $subject->forceFill(['is_active' => ! $subject->is_active])->save();

        return $subject;
    }

    public function delete(Subject $subject): void
    {
        $subject->delete();
    }
}
