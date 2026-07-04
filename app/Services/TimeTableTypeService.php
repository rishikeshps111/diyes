<?php

namespace App\Services;

use App\Models\TimeTableType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class TimeTableTypeService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return TimeTableType::query()
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return TimeTableType::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('time_table_type', TimeTableType::class);
    }

    public function create(array $data): TimeTableType
    {
        return TimeTableType::create([
            ...Arr::only($data, [
                'title',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(TimeTableType $timeTableType, array $data): TimeTableType
    {
        $timeTableType->update(Arr::only($data, [
            'title',
            'is_active',
        ]));

        return $timeTableType;
    }

    public function toggleStatus(TimeTableType $timeTableType): TimeTableType
    {
        $timeTableType->forceFill(['is_active' => ! $timeTableType->is_active])->save();

        return $timeTableType;
    }

    public function delete(TimeTableType $timeTableType): void
    {
        $timeTableType->delete();
    }
}
