<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class DivisionService
{
    public function query(array $filters = []): Builder
    {
        return Division::query()
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
        return Division::query()
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
        $lastCode = Division::query()
            ->where('code', 'like', 'DIV%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'DIV'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Division
    {
        return Division::create([
            ...Arr::only($data, [
                'division',
                'grade_id',
                'capacity',
                'class_teacher',
                'room_number',
                'is_active',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Division $division, array $data): Division
    {
        $division->update(Arr::only($data, [
            'division',
            'grade_id',
            'capacity',
            'class_teacher',
            'room_number',
            'is_active',
        ]));

        return $division;
    }

    public function toggleStatus(Division $division): Division
    {
        $division->forceFill(['is_active' => ! $division->is_active])->save();

        return $division;
    }

    public function delete(Division $division): void
    {
        $division->delete();
    }

}
