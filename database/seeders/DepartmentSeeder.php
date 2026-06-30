<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed master departments used by the timetable modules.
     */
    public function run(): void
    {
        $departments = [
            [
                'department_code' => 'DEP001',
                'department_name' => 'Mathematics',
                'department_head' => null,
                'description' => 'Mathematics and applied mathematics department.',
                'teacher_count' => 15,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'department_code' => 'DEP002',
                'department_name' => 'Science',
                'department_head' => 'Mathematics',
                'description' => 'Science department covering physics, chemistry, and biology.',
                'teacher_count' => 10,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'department_code' => 'DEP003',
                'department_name' => 'English',
                'department_head' => 'Mathematics',
                'description' => 'English language and literature department.',
                'teacher_count' => 8,
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'department_code' => 'DEP004',
                'department_name' => 'Social Studies',
                'department_head' => 'English',
                'description' => 'History, civics, geography, and social science department.',
                'teacher_count' => 7,
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'department_code' => 'DEP005',
                'department_name' => 'Computer Science',
                'department_head' => 'Science',
                'description' => 'Computer science and information technology department.',
                'teacher_count' => 6,
                'display_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                ['department_code' => $department['department_code']],
                [
                    'department_name' => $department['department_name'],
                    'department_head' => $department['department_head'],
                    'description' => $department['description'],
                    'teacher_count' => $department['teacher_count'],
                    'display_order' => $department['display_order'],
                    'is_active' => $department['is_active'],
                ],
            );
        }

    }
}
