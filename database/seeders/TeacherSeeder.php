<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\District;
use App\Models\Grade;
use App\Models\State;
use App\Models\Teacher;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::query()->where('code', 'IN')->first();
        $state = State::query()->where('name', 'Kerala')->first();

        if (! $country || ! $state) {
            return;
        }

        $district = District::query()
            ->where('state_id', $state->id)
            ->where('name', 'Ernakulam')
            ->first()
            ?? District::query()->where('state_id', $state->id)->first();

        $grades = Grade::query()->orderBy('grade')->get(['id', 'grade']);
        $departments = Department::query()->orderBy('department_name')->get(['id', 'department_name']);
        $designations = Designation::query()->orderBy('designation_name')->get(['id', 'designation_name']);

        if (! $district || $grades->isEmpty() || $departments->isEmpty() || $designations->isEmpty()) {
            return;
        }

        $departmentId = fn (string $name): int => $departments->firstWhere('department_name', $name)?->id
            ?? $departments->first()->id;
        $designationId = fn (string $name): int => $designations->firstWhere('designation_name', $name)?->id
            ?? $designations->first()->id;
        $gradeId = fn (int $index): int => $grades->get($index)?->id ?? $grades->first()->id;
        $codeService = app(PrefixCodeService::class);

        $teachers = [
            [
                'employee_id' => $codeService->format('teacher', 1),
                'name' => 'Anitha Joseph',
                'gender' => 'Female',
                'date_of_birth' => '1986-04-12',
                'phone' => '9876543210',
                'alternative_phone' => '9876501234',
                'email' => 'anitha.joseph@example.com',
                'qualification' => 'M.Sc Mathematics, B.Ed',
                'experience' => 12,
                'date_of_joining' => '2020-06-01',
                'department_id' => $departmentId('Mathematics'),
                'designation_id' => $designationId('Head of Department'),
                'subject' => 'Mathematics',
                'class_in_charge_id' => $gradeId(0),
                'employment_type' => 'permanent',
                'salary' => 62000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 2),
                'name' => 'Rahul Menon',
                'gender' => 'Male',
                'date_of_birth' => '1990-09-22',
                'phone' => '9847012345',
                'alternative_phone' => null,
                'email' => 'rahul.menon@example.com',
                'qualification' => 'M.Sc Physics, B.Ed',
                'experience' => 8,
                'date_of_joining' => '2021-04-15',
                'department_id' => $departmentId('Science'),
                'designation_id' => $designationId('Senior Teacher'),
                'subject' => 'Physics',
                'class_in_charge_id' => $gradeId(1),
                'employment_type' => 'permanent',
                'salary' => 54000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 3),
                'name' => 'Priya Nair',
                'gender' => 'Female',
                'date_of_birth' => '1993-01-18',
                'phone' => '9567890123',
                'alternative_phone' => '9567809876',
                'email' => 'priya.nair@example.com',
                'qualification' => 'MA English, B.Ed',
                'experience' => 6,
                'date_of_joining' => '2022-06-10',
                'department_id' => $departmentId('English'),
                'designation_id' => $designationId('Class Teacher'),
                'subject' => 'English',
                'class_in_charge_id' => $gradeId(2),
                'employment_type' => 'temporary',
                'salary' => 41000,
                'status' => 'Training',
                'is_verified' => false,
            ],
        ];

        foreach ($teachers as $teacher) {
            Teacher::query()->updateOrCreate(
                ['email' => $teacher['email']],
                [
                    ...$teacher,
                    'phone_country_code' => '+91',
                    'alternative_phone_country_code' => '+91',
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'district_id' => $district->id,
                    'address' => 'School Road, Kochi',
                    'pincode' => '682001',
                ],
            );
        }
    }
}
