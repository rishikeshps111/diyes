<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class GradeService
{
    public function query(array $filters = []): Builder
    {
        return Grade::query()
            ->with('academicYear')
            ->when($filters['academic_year_id'] ?? null, function (Builder $query, string $academicYearId): void {
                $query->where('academic_year_id', $academicYearId);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Grade::query()
            ->with('academicYear')
            ->whereKey($ids)
            ->orderByDesc('is_active')
            ->orderBy('grade')
            ->get();
    }

    public function academicYears(): Collection
    {
        return AcademicYear::query()
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get(['id', 'academic_year', 'is_active']);
    }

    public function nextCode(): string
    {
        $lastCode = Grade::query()
            ->where('code', 'like', 'GRD%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'GRD'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Grade
    {
        return Grade::create([
            ...Arr::only($data, [
                'grade',
                'capacity',
                'academic_year_id',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Grade $grade, array $data): Grade
    {
        $grade->update(Arr::only($data, [
            'grade',
            'capacity',
            'academic_year_id',
            'is_active',
        ]));

        return $grade;
    }

    public function toggleStatus(Grade $grade): Grade
    {
        $grade->forceFill(['is_active' => ! $grade->is_active])->save();

        return $grade;
    }

    public function delete(Grade $grade): void
    {
        $grade->delete();
    }

}
