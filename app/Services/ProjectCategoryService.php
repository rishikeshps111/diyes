<?php

namespace App\Services;

use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ProjectCategoryService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return ProjectCategory::query()
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return ProjectCategory::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('project_category', ProjectCategory::class);
    }

    public function create(array $data): ProjectCategory
    {
        return ProjectCategory::create([
            ...Arr::only($data, [
                'title',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(ProjectCategory $projectCategory, array $data): ProjectCategory
    {
        $projectCategory->update(Arr::only($data, [
            'title',
            'is_active',
        ]));

        return $projectCategory;
    }

    public function toggleStatus(ProjectCategory $projectCategory): ProjectCategory
    {
        $projectCategory->forceFill(['is_active' => ! $projectCategory->is_active])->save();

        return $projectCategory;
    }

    public function delete(ProjectCategory $projectCategory): void
    {
        $projectCategory->delete();
    }
}
