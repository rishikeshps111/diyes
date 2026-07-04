<?php

namespace Database\Seeders;

use App\Models\TimeTableType;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TimeTableTypeSeeder extends Seeder
{
    /**
     * Seed common time table types.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $timeTableTypes = [
            [
                'code' => $codeService->format('time_table_type', 1),
                'title' => 'Regular',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('time_table_type', 2),
                'title' => 'Exam',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('time_table_type', 3),
                'title' => 'Special',
                'is_active' => true,
            ],
        ];

        foreach ($timeTableTypes as $timeTableType) {
            TimeTableType::query()->updateOrCreate(
                ['title' => $timeTableType['title']],
                $timeTableType,
            );
        }
    }
}
