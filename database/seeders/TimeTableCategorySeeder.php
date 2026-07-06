<?php

namespace Database\Seeders;

use App\Models\TimeTableCategory;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TimeTableCategorySeeder extends Seeder
{
    /**
     * Seed common time table categories.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $timeTableCategories = [
            [
                'code' => $codeService->format('time_table_category', 1),
                'title' => 'Regular',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('time_table_category', 2),
                'title' => 'Exam',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('time_table_category', 3),
                'title' => 'Special',
                'is_active' => true,
            ],
        ];

        foreach ($timeTableCategories as $timeTableCategory) {
            TimeTableCategory::query()->updateOrCreate(
                ['title' => $timeTableCategory['title']],
                $timeTableCategory,
            );
        }
    }
}
