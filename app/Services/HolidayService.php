<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class HolidayService
{
    public function query(array $filters = []): Builder
    {
        return Holiday::query()
            ->with('academicYear')
            ->when($filters['applicable_branch'] ?? null, function (Builder $query, string $branch): void {
                $query->where('applicable_branch', 'like', "%{$branch}%");
            })
            ->when($filters['academic_year_id'] ?? null, function (Builder $query, string $academicYearId): void {
                $query->where('academic_year_id', $academicYearId);
            })
            ->when($filters['holiday_type'] ?? null, function (Builder $query, string $holidayType): void {
                $query->where('holiday_type', $holidayType);
            })
            ->when($filters['month'] ?? null, function (Builder $query, string $month): void {
                $query->whereMonth('holiday_date', $month);
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $dateFrom): void {
                $query->whereDate('holiday_date', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $dateTo): void {
                $query->whereDate('holiday_date', '<=', $dateTo);
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $query) use ($filters): void {
                $query->where('is_active', (bool) $filters['is_active']);
            });
    }

    public function selectedForExport(array $ids): Collection
    {
        return Holiday::query()
            ->with('academicYear')
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function academicYears(): Collection
    {
        return AcademicYear::query()
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get(['id', 'academic_year', 'is_active', 'start_date']);
    }

    public function holidayTypes(): array
    {
        return Holiday::HOLIDAY_TYPES;
    }

    public function applicableClasses(): array
    {
        return Holiday::APPLICABLE_CLASSES;
    }

    public function months(): array
    {
        return [
            '1' => 'January',
            '2' => 'February',
            '3' => 'March',
            '4' => 'April',
            '5' => 'May',
            '6' => 'June',
            '7' => 'July',
            '8' => 'August',
            '9' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ];
    }

    public function nextCode(): string
    {
        $lastCode = Holiday::query()
            ->where('code', 'like', 'HOL%')
            ->orderByDesc('id')
            ->value('code');

        $nextNumber = $lastCode ? ((int) preg_replace('/\D/', '', $lastCode)) + 1 : 1;

        return 'HOL'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Holiday
    {
        return Holiday::create([
            ...Arr::only($data, [
                'holiday_name',
                'holiday_type',
                'academic_year_id',
                'holiday_date',
                'start_date',
                'end_date',
                'applicable_branch',
                'applicable_classes',
                'is_active',
                'description',
            ]),
            'code' => $this->nextCode(),
        ]);
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        $holiday->update(Arr::only($data, [
            'holiday_name',
            'holiday_type',
            'academic_year_id',
            'holiday_date',
            'start_date',
            'end_date',
            'applicable_branch',
            'applicable_classes',
            'is_active',
            'description',
        ]));

        return $holiday;
    }

    public function toggleStatus(Holiday $holiday): Holiday
    {
        $holiday->forceFill(['is_active' => ! $holiday->is_active])->save();

        return $holiday;
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
