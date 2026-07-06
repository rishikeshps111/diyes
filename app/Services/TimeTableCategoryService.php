<?php

namespace App\Services;

use App\Models\TimeTableCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class TimeTableCategoryService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return TimeTableCategory::query()
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return TimeTableCategory::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('time_table_category', TimeTableCategory::class);
    }

    public function create(array $data): TimeTableCategory
    {
        return TimeTableCategory::create([
            ...Arr::only($data, [
                'title',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(TimeTableCategory $timeTableCategory, array $data): TimeTableCategory
    {
        $timeTableCategory->update(Arr::only($data, [
            'title',
            'is_active',
        ]));

        return $timeTableCategory;
    }

    public function toggleStatus(TimeTableCategory $timeTableCategory): TimeTableCategory
    {
        $timeTableCategory->forceFill(['is_active' => ! $timeTableCategory->is_active])->save();

        return $timeTableCategory;
    }

    public function delete(TimeTableCategory $timeTableCategory): void
    {
        $timeTableCategory->delete();
    }
}
