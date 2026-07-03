<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Holiday;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Seed common holidays.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->active()
            ->first()
            ?? AcademicYear::query()->orderByDesc('start_date')->first();

        if (! $academicYear) {
            return;
        }

        $year = (int) $academicYear->start_date?->format('Y') ?: 2026;
        $codeService = app(PrefixCodeService::class);

        $holidays = [
            [
                'code' => $codeService->format('holiday', 1),
                'holiday_name' => 'Independence Day',
                'holiday_type' => 'Public',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-08-15',
                'start_date' => $year.'-08-15',
                'end_date' => $year.'-08-15',
                'applicable_for' => 'All',
                'is_active' => true,
                'description' => 'National holiday for Independence Day.',
            ],
            [
                'code' => $codeService->format('holiday', 2),
                'holiday_name' => 'Onam Break',
                'holiday_type' => 'festival',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-09-05',
                'start_date' => $year.'-09-05',
                'end_date' => $year.'-09-08',
                'applicable_for' => 'All',
                'is_active' => true,
                'description' => 'Festival break for Onam celebrations.',
            ],
            [
                'code' => $codeService->format('holiday', 3),
                'holiday_name' => 'Christmas Vacation',
                'holiday_type' => 'Others',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-12-24',
                'start_date' => $year.'-12-24',
                'end_date' => $year.'-12-31',
                'applicable_for' => 'All',
                'is_active' => true,
                'description' => 'Year-end vacation.',
            ],
            [
                'code' => $codeService->format('holiday', 4),
                'holiday_name' => 'Teachers Training Day',
                'holiday_type' => 'Optional',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => ($year + 1).'-01-10',
                'start_date' => ($year + 1).'-01-10',
                'end_date' => ($year + 1).'-01-10',
                'applicable_for' => 'Teaching Staff',
                'is_active' => true,
                'description' => 'Holiday for selected classes during staff training.',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::query()->updateOrCreate(
                [
                    'holiday_name' => $holiday['holiday_name'],
                    'academic_year_id' => $holiday['academic_year_id'],
                ],
                $holiday,
            );
        }
    }
}
