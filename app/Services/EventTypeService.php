<?php

namespace App\Services;

use App\Models\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class EventTypeService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return EventType::query()
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return EventType::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('event_type', EventType::class);
    }

    public function create(array $data): EventType
    {
        return EventType::create([
            ...Arr::only($data, [
                'title',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(EventType $eventType, array $data): EventType
    {
        $eventType->update(Arr::only($data, [
            'title',
            'is_active',
        ]));

        return $eventType;
    }

    public function toggleStatus(EventType $eventType): EventType
    {
        $eventType->forceFill(['is_active' => ! $eventType->is_active])->save();

        return $eventType;
    }

    public function delete(EventType $eventType): void
    {
        $eventType->delete();
    }
}
