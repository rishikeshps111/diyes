<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Seed academic years.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $academicYears = [
            [
                'code' => $codeService->format('academic_year', 1),
                'academic_year' => '2025-2026',
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'is_active' => false,
                'description' => 'Previous academic year.',
            ],
            [
                'code' => $codeService->format('academic_year', 2),
                'academic_year' => '2026-2027',
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_active' => true,
                'description' => 'Current academic year.',
            ],
            [
                'code' => $codeService->format('academic_year', 3),
                'academic_year' => '2027-2028',
                'start_date' => '2027-04-01',
                'end_date' => '2028-03-31',
                'is_active' => false,
                'description' => 'Upcoming academic year.',
            ],
        ];

        foreach ($academicYears as $academicYear) {
            AcademicYear::query()->updateOrCreate(
                ['academic_year' => $academicYear['academic_year']],
                $academicYear,
            );
        }

        $activeAcademicYear = collect($academicYears)->firstWhere('is_active', true);

        if ($activeAcademicYear) {
            AcademicYear::query()
                ->where('academic_year', '!=', $activeAcademicYear['academic_year'])
                ->update(['is_active' => false]);
        }
    }
}
