<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function query(array $filters = []): Builder
    {
        return AcademicYear::query()
            ->when($filters['academic_year'] ?? null, function (Builder $query, string $academicYear): void {
                $query->where('academic_year', 'like', "%{$academicYear}%");
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return AcademicYear::query()
            ->whereKey($ids)
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();
    }

    public function nextCode(): string
    {
        $lastCode = AcademicYear::query()
            ->where('code', 'like', 'AY%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'AY'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data): AcademicYear {
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                $this->deactivateOthers();
            }

            return AcademicYear::create([
                ...Arr::only($data, [
                    'academic_year',
                    'start_date',
                    'end_date',
                    'is_active',
                    'description',
                ]),
                'code' => $this->nextCode(),
            ]);
        });
    }

    public function update(AcademicYear $academicYear, array $data): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $data): AcademicYear {
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                $this->deactivateOthers($academicYear);
            }

            $academicYear->update(Arr::only($data, [
                'academic_year',
                'start_date',
                'end_date',
                'is_active',
                'description',
            ]));

            return $academicYear;
        });
    }

    public function toggleStatus(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear): AcademicYear {
            $newStatus = ! $academicYear->is_active;

            if ($newStatus) {
                $this->deactivateOthers($academicYear);
            }

            $academicYear->forceFill(['is_active' => $newStatus])->save();

            return $academicYear;
        });
    }

    public function delete(AcademicYear $academicYear): void
    {
        $academicYear->delete();
    }

    private function deactivateOthers(?AcademicYear $exceptAcademicYear = null): void
    {
        AcademicYear::query()
            ->when($exceptAcademicYear, fn (Builder $query) => $query->whereKeyNot($exceptAcademicYear->id))
            ->update(['is_active' => false]);
    }
}
