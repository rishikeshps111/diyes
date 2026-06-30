<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class DesignationService
{
    public function query(array $filters = []): Builder
    {
        return Designation::query()
            ->with(['department', 'grade.academicYear'])
            ->when($filters['department_id'] ?? null, function (Builder $query, string $departmentId): void {
                $query->where('department_id', $departmentId);
            })
            ->when($filters['designation_name'] ?? null, function (Builder $query, string $designationName): void {
                $query->where('designation_name', 'like', "%{$designationName}%");
            })
            ->when($filters['grade_id'] ?? null, function (Builder $query, string $gradeId): void {
                $query->where('grade_id', $gradeId);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Designation::query()
            ->with(['department', 'grade.academicYear'])
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function departments(): Collection
    {
        return Department::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name']);
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
        $lastCode = Designation::query()
            ->where('code', 'like', 'DSG%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'DSG'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Designation
    {
        return Designation::create([
            ...Arr::only($data, [
                'designation_name',
                'department_id',
                'grade_id',
                'is_active',
                'description',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Designation $designation, array $data): Designation
    {
        $designation->update(Arr::only($data, [
            'designation_name',
            'department_id',
            'grade_id',
            'is_active',
            'description',
        ]));

        return $designation;
    }

    public function toggleStatus(Designation $designation): Designation
    {
        $designation->forceFill(['is_active' => ! $designation->is_active])->save();

        return $designation;
    }

    public function delete(Designation $designation): void
    {
        $designation->delete();
    }
}
