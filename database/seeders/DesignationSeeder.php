<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Seed common staff designations.
     */
    public function run(): void
    {
        $departments = Department::query()
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        $grades = Grade::query()
            ->orderBy('grade')
            ->get(['id']);

        if ($departments->isEmpty() || $grades->isEmpty()) {
            return;
        }

        $departmentId = fn (string $name): int => $departments->firstWhere('department_name', $name)?->id
            ?? $departments->first()->id;

        $designations = [
            [
                'code' => 'DSG001',
                'designation_name' => 'Head of Department',
                'department_id' => $departmentId('Mathematics'),
                'grade_id' => $grades->get(0)?->id,
                'is_active' => true,
                'description' => 'Department leadership and academic planning.',
            ],
            [
                'code' => 'DSG002',
                'designation_name' => 'Senior Teacher',
                'department_id' => $departmentId('Science'),
                'grade_id' => $grades->get(1)?->id ?? $grades->first()->id,
                'is_active' => true,
                'description' => 'Senior teaching role with mentoring responsibilities.',
            ],
            [
                'code' => 'DSG003',
                'designation_name' => 'Class Teacher',
                'department_id' => $departmentId('English'),
                'grade_id' => $grades->get(2)?->id ?? $grades->first()->id,
                'is_active' => true,
                'description' => 'Class supervision and parent communication.',
            ],
            [
                'code' => 'DSG004',
                'designation_name' => 'Assistant Teacher',
                'department_id' => $departmentId('Computer Science'),
                'grade_id' => $grades->get(3)?->id ?? $grades->first()->id,
                'is_active' => true,
                'description' => 'Teaching support and lesson assistance.',
            ],
        ];

        foreach ($designations as $designation) {
            Designation::query()->updateOrCreate(
                ['code' => $designation['code']],
                $designation,
            );
        }
    }
}
