<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Services\PrefixCodeService;
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

        $codeService = app(PrefixCodeService::class);

        $grades = [
            ['code' => $codeService->format('grade', 1), 'grade' => 'Grade 1', 'capacity' => 40, 'is_active' => true],
            ['code' => $codeService->format('grade', 2), 'grade' => 'Grade 2', 'capacity' => 40, 'is_active' => true],
            ['code' => $codeService->format('grade', 3), 'grade' => 'Grade 3', 'capacity' => 38, 'is_active' => true],
            ['code' => $codeService->format('grade', 4), 'grade' => 'Grade 4', 'capacity' => 38, 'is_active' => true],
            ['code' => $codeService->format('grade', 5), 'grade' => 'Grade 5', 'capacity' => 36, 'is_active' => true],
        ];

        foreach ($grades as $grade) {
            Grade::query()->updateOrCreate(
                [
                    'grade' => $grade['grade'],
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'code' => $grade['code'],
                    'grade' => $grade['grade'],
                    'capacity' => $grade['capacity'],
                    'academic_year_id' => $academicYear->id,
                    'is_active' => $grade['is_active'],
                ],
            );
        }

    }
}
