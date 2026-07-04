<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Seed common classrooms and labs.
     */
    public function run(): void
    {
        $codeService = app(PrefixCodeService::class);

        $classrooms = [
            [
                'code' => $codeService->format('classroom', 1),
                'room_name' => 'Room 101',
                'building' => 'Main Block',
                'floor' => 'First Floor',
                'room_type' => 'Smart Classroom',
                'seating_capacity' => 40,
                'facilities' => ['Projector', 'Interactive Board', 'Wi-Fi'],
                'is_active' => true,
                'remarks' => 'Primary smart classroom for middle school.',
            ],
            [
                'code' => $codeService->format('classroom', 2),
                'room_name' => 'Science Lab',
                'building' => 'Science Block',
                'floor' => 'Ground Floor',
                'room_type' => 'Laboratory',
                'seating_capacity' => 30,
                'facilities' => ['Lab Benches', 'Whiteboard', 'CCTV'],
                'is_active' => true,
                'remarks' => 'Shared physics and chemistry lab.',
            ],
            [
                'code' => $codeService->format('classroom', 3),
                'room_name' => 'Computer Lab A',
                'building' => 'IT Block',
                'floor' => 'Second Floor',
                'room_type' => 'Computer Lab',
                'seating_capacity' => 35,
                'facilities' => ['Computer', 'Projector', 'Air Conditioner', 'Wi-Fi'],
                'is_active' => true,
                'remarks' => 'Computer practical classroom.',
            ],
            [
                'code' => $codeService->format('classroom', 4),
                'room_name' => 'Seminar Hall',
                'building' => 'Admin Block',
                'floor' => 'First Floor',
                'room_type' => 'Seminar Hall',
                'seating_capacity' => 80,
                'facilities' => ['Projector', 'Audio System', 'Air Conditioner'],
                'is_active' => true,
                'remarks' => 'Used for workshops and training sessions.',
            ],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::query()->updateOrCreate(
                ['room_name' => $classroom['room_name']],
                $classroom,
            );
        }
    }
}
