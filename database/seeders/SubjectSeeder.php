<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Seed common subjects for available grades.
     */
    public function run(): void
    {
        $grade = Grade::query()->orderBy('grade')->first();

        if (! $grade) {
            return;
        }

        $codeService = app(PrefixCodeService::class);

        $subjects = [
            [
                'subject_code' => $codeService->format('subject', 1),
                'subject_name' => 'Mathematics',
                'grade_id' => $grade->id,
                'color' => '#dbeafe',
                'is_active' => true,
                'priority' => 'high',
                'is_praticals' => false,
            ],
            [
                'subject_code' => $codeService->format('subject', 2),
                'subject_name' => 'Science',
                'grade_id' => $grade->id,
                'color' => '#dcfce7',
                'is_active' => true,
                'priority' => 'high',
                'is_praticals' => true,
            ],
            [
                'subject_code' => $codeService->format('subject', 3),
                'subject_name' => 'English',
                'grade_id' => $grade->id,
                'color' => '#fef3c7',
                'is_active' => true,
                'priority' => 'medium',
                'is_praticals' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(
                [
                    'subject_name' => $subject['subject_name'],
                    'grade_id' => $subject['grade_id'],
                ],
                $subject,
            );
        }
    }
}
