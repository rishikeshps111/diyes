<?php

namespace Database\Seeders;

use App\Models\TrainerType;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TrainerTypeSeeder extends Seeder
{
    /**
     * Seed the default trainer types.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);
        $titles = [
            'Academic',
            'Arts & Culture',
            'Sports',
            'Life Skills',
            'Technology',
            'Special Programs',
        ];

        foreach ($titles as $index => $title) {
            TrainerType::query()->updateOrCreate(
                ['title' => $title],
                [
                    'code' => $codeService->format('trainer_type', $index + 1),
                    'is_active' => true,
                ],
            );
        }
    }
}
