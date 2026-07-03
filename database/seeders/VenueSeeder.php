<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Seed common school venues.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $venues = [
            [
                'code' => $codeService->format('venue', 1),
                'venue_name' => 'Main Auditorium',
                'venue_type' => 'Auditorium',
                'building' => 'Admin Block',
                'capacity' => 500,
                'facilities' => ['Stage', 'Sound System', 'Lighting', 'Air Conditioner'],
                'contact_person' => 'Arun Thomas',
                'is_active' => true,
                'remarks' => 'Used for annual day and major events.',
            ],
            [
                'code' => $codeService->format('venue', 2),
                'venue_name' => 'Conference Hall',
                'venue_type' => 'Conference Room',
                'building' => 'Main Block',
                'capacity' => 80,
                'facilities' => ['Projector', 'Wi-Fi', 'Podium', 'Seating'],
                'contact_person' => 'Meera Nair',
                'is_active' => true,
                'remarks' => 'Parent meetings and staff conferences.',
            ],
            [
                'code' => $codeService->format('venue', 3),
                'venue_name' => 'Sports Ground',
                'venue_type' => 'Sports Ground',
                'building' => 'Outdoor Campus',
                'capacity' => 1200,
                'facilities' => ['Parking', 'Sound System', 'Seating'],
                'contact_person' => 'Ramesh Kumar',
                'is_active' => true,
                'remarks' => 'Sports day and outdoor assemblies.',
            ],
            [
                'code' => $codeService->format('venue', 4),
                'venue_name' => 'Activity Hall',
                'venue_type' => 'Hall',
                'building' => 'Primary Block',
                'capacity' => 150,
                'facilities' => ['Audio System', 'Projector', 'Air Conditioner'],
                'contact_person' => 'Lina Mathew',
                'is_active' => true,
                'remarks' => 'Co-curricular activity sessions.',
            ],
        ];

        foreach ($venues as $venue) {
            Venue::query()->updateOrCreate(
                ['venue_name' => $venue['venue_name']],
                $venue,
            );
        }
    }
}
