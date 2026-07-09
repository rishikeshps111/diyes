<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Seed common event types.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $eventTypes = [
            [
                'code' => $codeService->format('event_type', 1),
                'title' => 'Sports',
                'is_active' => true,
            ],
            [
                'code' => $codeService->format('event_type', 2),
                'title' => 'Academic',
                'is_active' => true,
            ],
        ];

        foreach ($eventTypes as $eventType) {
            EventType::query()->updateOrCreate(
                ['title' => $eventType['title']],
                $eventType,
            );
        }
    }
}
