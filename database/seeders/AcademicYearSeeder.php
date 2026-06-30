<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Seed academic years.
     */
    public function run(): void
    {
        $academicYears = [
            [
                'code' => 'AY001',
                'academic_year' => '2025-2026',
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_active' => false,
                'description' => 'Previous academic year.',
            ],
            [
                'code' => 'AY002',
                'academic_year' => '2026-2027',
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_active' => true,
                'description' => 'Current academic year.',
            ],
            [
                'code' => 'AY003',
                'academic_year' => '2027-2028',
                'start_date' => '2027-04-01',
                'end_date' => '2028-03-31',
                'is_active' => false,
                'description' => 'Upcoming academic year.',
            ],
        ];

        foreach ($academicYears as $academicYear) {
            AcademicYear::query()->updateOrCreate(
                ['code' => $academicYear['code']],
                $academicYear,
            );
        }

        $activeAcademicYear = collect($academicYears)->firstWhere('is_active', true);

        if ($activeAcademicYear) {
            AcademicYear::query()
                ->where('code', '!=', $activeAcademicYear['code'])
                ->update(['is_active' => false]);
        }
    }
}
