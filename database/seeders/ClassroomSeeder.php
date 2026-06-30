<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Seed common classrooms and labs.
     */
    public function run(): void
    {
        $departments = Department::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        if ($departments->isEmpty()) {
            return;
        }

        $departmentId = fn (string $name): int => $departments->firstWhere('department_name', $name)?->id
            ?? $departments->first()->id;

        $classrooms = [
            [
                'code' => 'CLS001',
                'room_name' => 'Room 101',
                'building' => 'Main Block',
                'floor' => 'First Floor',
                'room_type' => 'Smart Classroom',
                'seating_capacity' => 40,
                'department_id' => $departmentId('Mathematics'),
                'equipment' => ['Projector', 'Interactive Board', 'Wi-Fi'],
                'is_active' => true,
                'remarks' => 'Primary smart classroom for middle school.',
            ],
            [
                'code' => 'CLS002',
                'room_name' => 'Science Lab',
                'building' => 'Science Block',
                'floor' => 'Ground Floor',
                'room_type' => 'Laboratory',
                'seating_capacity' => 30,
                'department_id' => $departmentId('Science'),
                'equipment' => ['Lab Benches', 'Whiteboard', 'CCTV'],
                'is_active' => true,
                'remarks' => 'Shared physics and chemistry lab.',
            ],
            [
                'code' => 'CLS003',
                'room_name' => 'Computer Lab A',
                'building' => 'IT Block',
                'floor' => 'Second Floor',
                'room_type' => 'Computer Lab',
                'seating_capacity' => 35,
                'department_id' => $departmentId('Computer Science'),
                'equipment' => ['Computer', 'Projector', 'Air Conditioner', 'Wi-Fi'],
                'is_active' => true,
                'remarks' => 'Computer practical classroom.',
            ],
            [
                'code' => 'CLS004',
                'room_name' => 'Seminar Hall',
                'building' => 'Admin Block',
                'floor' => 'First Floor',
                'room_type' => 'Seminar Hall',
                'seating_capacity' => 80,
                'department_id' => $departmentId('English'),
                'equipment' => ['Projector', 'Audio System', 'Air Conditioner'],
                'is_active' => true,
                'remarks' => 'Used for workshops and training sessions.',
            ],
        ];

        foreach ($classrooms as $classroom) {
            Classroom::query()->updateOrCreate(
                ['code' => $classroom['code']],
                $classroom,
            );
        }
    }
}
