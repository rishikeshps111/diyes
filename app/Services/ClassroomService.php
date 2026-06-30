<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ClassroomService
{
    public function query(array $filters = []): Builder
    {
        return Classroom::query()
            ->with('department')
            ->when($filters['building'] ?? null, function (Builder $query, string $building): void {
                $query->where('building', 'like', "%{$building}%");
            })
            ->when($filters['floor'] ?? null, function (Builder $query, string $floor): void {
                $query->where('floor', 'like', "%{$floor}%");
            })
            ->when($filters['room_type'] ?? null, function (Builder $query, string $roomType): void {
                $query->where('room_type', $roomType);
            })
            ->when($filters['department_id'] ?? null, function (Builder $query, string $departmentId): void {
                $query->where('department_id', $departmentId);
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
            ->with('department')
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

    public function roomTypes(): array
    {
        return Classroom::ROOM_TYPES;
    }

    public function equipmentOptions(): array
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
        $lastCode = Classroom::query()
            ->where('code', 'like', 'CLS%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'CLS'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
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
                'department_id',
                'equipment',
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
            'department_id',
            'equipment',
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
