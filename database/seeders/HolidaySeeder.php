<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Holiday;
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

        $holidays = [
            [
                'code' => 'HOL001',
                'holiday_name' => 'Independence Day',
                'holiday_type' => 'National',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-08-15',
                'start_date' => $year.'-08-15',
                'end_date' => $year.'-08-15',
                'applicable_branch' => 'Main Branch',
                'applicable_classes' => 'All Classes',
                'is_active' => true,
                'description' => 'National holiday for Independence Day.',
            ],
            [
                'code' => 'HOL002',
                'holiday_name' => 'Onam Break',
                'holiday_type' => 'Festival',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-09-05',
                'start_date' => $year.'-09-05',
                'end_date' => $year.'-09-08',
                'applicable_branch' => 'Main Branch',
                'applicable_classes' => 'All Classes',
                'is_active' => true,
                'description' => 'Festival break for Onam celebrations.',
            ],
            [
                'code' => 'HOL003',
                'holiday_name' => 'Christmas Vacation',
                'holiday_type' => 'Vacation',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => $year.'-12-24',
                'start_date' => $year.'-12-24',
                'end_date' => $year.'-12-31',
                'applicable_branch' => null,
                'applicable_classes' => 'All Classes',
                'is_active' => true,
                'description' => 'Year-end vacation.',
            ],
            [
                'code' => 'HOL004',
                'holiday_name' => 'Teachers Training Day',
                'holiday_type' => 'School Event',
                'academic_year_id' => $academicYear->id,
                'holiday_date' => ($year + 1).'-01-10',
                'start_date' => ($year + 1).'-01-10',
                'end_date' => ($year + 1).'-01-10',
                'applicable_branch' => 'Senior Wing',
                'applicable_classes' => 'High School',
                'is_active' => true,
                'description' => 'Holiday for selected classes during staff training.',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::query()->updateOrCreate(
                ['code' => $holiday['code']],
                $holiday,
            );
        }
    }
}
