<?php

namespace Database\Seeders;

use App\Models\TrainerCategory;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TrainerCategorySeeder extends Seeder
{
    /**
     * Seed the default trainer categories.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);
        $titles = [
            'Fitness Trainer',
            'Yoga Instructor',
            'Personal Trainer',
            'Nutritionist',
            'Pilates Instructor',
            'Martial Arts Instructor',
            'Dance Instructor',
            'Sports Coach',
            'Wellness Coach',
            'Rehabilitation Specialist'
        ];

        foreach ($titles as $index => $title) {
            TrainerCategory::query()->updateOrCreate(
                ['title' => $title],
                [
                    'code' => $codeService->format('trainer_category', $index + 1),
                    'is_active' => true,
                ],
            );
        }
    }
}
