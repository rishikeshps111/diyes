<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Seed grades for the current academic year.
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

        $grades = [
            ['code' => 'GRD001', 'grade' => 'Grade 1', 'capacity' => 40, 'is_active' => true],
            ['code' => 'GRD002', 'grade' => 'Grade 2', 'capacity' => 40, 'is_active' => true],
            ['code' => 'GRD003', 'grade' => 'Grade 3', 'capacity' => 38, 'is_active' => true],
            ['code' => 'GRD004', 'grade' => 'Grade 4', 'capacity' => 38, 'is_active' => true],
            ['code' => 'GRD005', 'grade' => 'Grade 5', 'capacity' => 36, 'is_active' => true],
        ];

        foreach ($grades as $grade) {
            Grade::query()->updateOrCreate(
                ['code' => $grade['code']],
                [
                    'grade' => $grade['grade'],
                    'capacity' => $grade['capacity'],
                    'academic_year_id' => $academicYear->id,
                    'is_active' => $grade['is_active'],
                ],
            );
        }

    }
}
