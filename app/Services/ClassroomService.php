<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ClassroomService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Classroom::query()
            ->when($filters['building'] ?? null, function (Builder $query, string $building): void {
                $query->where('building', 'like', "%{$building}%");
            })
            ->when($filters['floor'] ?? null, function (Builder $query, string $floor): void {
                $query->where('floor', 'like', "%{$floor}%");
            })
            ->when($filters['room_type'] ?? null, function (Builder $query, string $roomType): void {
                $query->where('room_type', $roomType);
            })
            ->when($filters['seating_capacity'] ?? null, function (Builder $query, string $capacity): void {
                $query->where('seating_capacity', $capacity);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Classroom::query()
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function roomTypes(): array
    {
        return Classroom::ROOM_TYPES;
    }

    public function facilityOptions(): array
    {
        return [
            'Projector',
            'Interactive Board',
            'Whiteboard',
            'Audio System',
            'Document Camera',
            'Computer',
            'Air Conditioner',
            'Wi-Fi',
            'Lab Benches',
            'CCTV',
        ];
    }

    public function nextCode(): string
    {
        return $this->prefixCodeService->next('classroom', Classroom::class);
    }

    public function create(array $data): Classroom
    {
        return Classroom::create([
            ...Arr::only($data, [
                'room_name',
                'building',
                'floor',
                'room_type',
                'seating_capacity',
                'facilities',
                'is_active',
                'remarks',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Classroom $classroom, array $data): Classroom
    {
        $classroom->update(Arr::only($data, [
            'room_name',
            'building',
            'floor',
            'room_type',
            'seating_capacity',
            'facilities',
            'is_active',
            'remarks',
        ]));

        return $classroom;
    }

    public function toggleStatus(Classroom $classroom): Classroom
    {
        $classroom->forceFill(['is_active' => ! $classroom->is_active])->save();

        return $classroom;
    }

    public function delete(Classroom $classroom): void
    {
        $classroom->delete();
    }
}
