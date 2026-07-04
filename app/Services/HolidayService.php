<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class HolidayService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return Holiday::query()
            ->with('academicYear')
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
            ->when($filters['applicable_for'] ?? null, function (Builder $query, string $applicableFor): void {
                $query->where('applicable_for', $applicableFor);
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
            ->get(['id', 'academic_year', 'is_active', 'start_date', 'end_date']);
    }

    public function holidayTypes(): array
    {
        return Holiday::HOLIDAY_TYPES;
    }

    public function holidayTypeColors(): array
    {
        return [
            'Public' => '#d32f2f',
            'festival' => '#f57c00',
            'Optional' => '#1976d2',
            'Others' => '#6a1b9a',
        ];
    }

    public function applicableForOptions(): array
    {
        return Holiday::APPLICABLE_FOR;
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
        return $this->prefixCodeService->next('holiday', Holiday::class);
    }

    public function calendar(?int $academicYearId = null): array
    {
        $academicYear = AcademicYear::query()
            ->when($academicYearId, fn (Builder $query) => $query->whereKey($academicYearId))
            ->when(! $academicYearId, fn (Builder $query) => $query->where('is_active', true))
            ->orderByDesc('start_date')
            ->first()
            ?? AcademicYear::query()->orderByDesc('start_date')->first();

        $startDate = $academicYear?->start_date
            ? Carbon::parse($academicYear->start_date)->startOfMonth()
            : now()->copy()->startOfYear();
        $endDate = $academicYear?->end_date
            ? Carbon::parse($academicYear->end_date)->endOfMonth()
            : now()->copy()->endOfYear();

        $holidays = Holiday::query()
            ->with('academicYear')
            ->where('is_active', true)
            ->when($academicYear, fn (Builder $query) => $query->where('academic_year_id', $academicYear->id))
            ->whereDate('end_date', '>=', $startDate)
            ->whereDate('start_date', '<=', $endDate)
            ->orderBy('start_date')
            ->get();

        return [
            'academicYear' => $academicYear,
            'initialDate' => now()->betweenIncluded($startDate, $endDate) ? now()->toDateString() : $startDate->toDateString(),
            'validRange' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->copy()->addDay()->toDateString(),
            ],
            'events' => [
                ...$this->weeklyOffEvents($startDate, $endDate),
                ...$this->holidayEvents($holidays),
            ],
        ];
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
                'applicable_for',
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
            'applicable_for',
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

    private function holidayEvents(Collection $holidays): array
    {
        $colors = $this->holidayTypeColors();

        return $holidays
            ->map(function (Holiday $holiday) use ($colors): array {
                $color = $colors[$holiday->holiday_type] ?? '#c62828';

                return [
                    'title' => $holiday->holiday_name,
                    'start' => $holiday->start_date?->toDateString(),
                    'end' => $holiday->end_date?->copy()->addDay()->toDateString(),
                    'allDay' => true,
                    'color' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'kind' => 'holiday',
                        'type' => $holiday->holiday_type,
                        'typeColor' => $color,
                        'holidayDate' => $holiday->holiday_date?->format('d M Y') ?? '-',
                        'range' => $holiday->start_date?->format('d M Y').' - '.$holiday->end_date?->format('d M Y'),
                        'applicableFor' => $holiday->applicable_for ?: '-',
                        'description' => $holiday->description ?: '-',
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function weeklyOffEvents(Carbon $startDate, Carbon $endDate): array
    {
        $events = [];
        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            $isSunday = $date->isSunday();
            $isSecondSaturday = $date->isSaturday() && $date->day >= 8 && $date->day <= 14;

            if ($isSunday || $isSecondSaturday) {
                $events[] = [
                    'title' => $isSunday ? 'Sunday' : 'Second Saturday',
                    'start' => $date->toDateString(),
                    'allDay' => true,
                    'display' => 'background',
                    'backgroundColor' => '#fff1f1',
                    'extendedProps' => [
                        'kind' => 'weekly_off',
                        'description' => $isSunday ? 'Sunday holiday' : 'Second Saturday holiday',
                    ],
                ];
            }

            $date->addDay();
        }

        return $events;
    }

    public function applicableForText(?string $applicableFor): string
    {
        return $applicableFor ?: '-';
    }
}
