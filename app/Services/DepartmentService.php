<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class DepartmentService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Department::query()
            ->when($filters['department_name'] ?? null, function (Builder $query, string $departmentName): void {
                $query->where('department_name', 'like', "%{$departmentName}%");
            })
            ->when($filters['department_head'] ?? null, function (Builder $query, string $departmentHead): void {
                $query->where('department_head', 'like', "%{$departmentHead}%");
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Department::query()
            ->whereKey($ids)
            ->orderBy('display_order')
            ->orderBy('department_name')
            ->get();
    }

    public function nextDepartmentCode(): string
    {
        return $this->prefixCodeService->next('department', Department::class, 'department_code');
    }

    public function create(array $data): Department
    {
        return Department::create([
            ...Arr::only($data, [
                'department_name',
                'department_head',
                'description',
                'teacher_count',
                'display_order',
                'is_active',
            ]),
            'department_code' => $this->nextDepartmentCode(),
        ]);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update(Arr::only($data, [
            'department_name',
            'department_head',
            'description',
            'teacher_count',
            'display_order',
            'is_active',
        ]));

        return $department;
    }

    public function toggleStatus(Department $department): Department
    {
        $department->forceFill(['is_active' => ! $department->is_active])->save();

        return $department;
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }
}
