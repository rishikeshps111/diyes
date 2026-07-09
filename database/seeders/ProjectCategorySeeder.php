<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    /**
     * Seed common project categories.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $projectCategories = [
            [
                'code' => $codeService->format('project_category', 1),
                'title' => 'Academic',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('project_category', 2),
                'title' => 'Infrastructure',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('project_category', 3),
                'title' => 'Event',
                'is_active' => true,
            ],
        ];

        foreach ($projectCategories as $projectCategory) {
            ProjectCategory::query()->updateOrCreate(
                ['title' => $projectCategory['title']],
                $projectCategory,
            );
        }
    }
}
