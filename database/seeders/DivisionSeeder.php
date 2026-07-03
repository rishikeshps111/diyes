<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Grade;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Seed divisions for available grades.
     */
    public function run(): void
    {
        $grades = Grade::query()
            ->orderBy('grade')
            ->limit(3)
            ->get(['id']);

        if ($grades->isEmpty()) {
            return;
        }

        $codeService = app(PrefixCodeService::class);

        $divisions = [
            [
                'code' => $codeService->format('division', 1),
                'division' => 'A',
                'grade_id' => $grades->get(0)?->id,
                'capacity' => 40,
                'class_teacher' => 'Anitha Joseph',
                'room_number' => 101,
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('division', 2),
                'division' => 'B',
                'grade_id' => $grades->get(0)?->id,
                'capacity' => 40,
                'class_teacher' => 'Rahul Menon',
                'room_number' => 102,
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('division', 3),
                'division' => 'A',
                'grade_id' => $grades->get(1)?->id ?? $grades->get(0)?->id,
                'capacity' => 38,
                'class_teacher' => 'Priya Nair',
                'room_number' => 201,
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('division', 4),
                'division' => 'A',
                'grade_id' => $grades->get(2)?->id ?? $grades->get(0)?->id,
                'capacity' => 36,
                'class_teacher' => 'Vivek Kumar',
                'room_number' => 301,
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $division) {
            Division::query()->updateOrCreate(
                [
                    'division' => $division['division'],
                    'grade_id' => $division['grade_id'],
                ],
                $division,
            );
        }
    }
}
