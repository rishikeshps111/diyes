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
            [
                'employee_id' => $codeService->format('teacher', 4),
                'name' => 'Arjun Kumar',
                'gender' => 'Male',
                'date_of_birth' => '1988-07-05',
                'phone' => '9895123401',
                'alternative_phone' => null,
                'email' => 'arjun.kumar@example.com',
                'qualification' => 'M.Sc Chemistry, B.Ed',
                'experience' => 10,
                'date_of_joining' => '2019-06-03',
                'department_id' => $departmentId('Science'),
                'designation_id' => $designationId('Senior Teacher'),
                'subject' => 'Chemistry',
                'class_in_charge_id' => $gradeId(3),
                'employment_type' => 'permanent',
                'salary' => 57000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 5),
                'name' => 'Meera Thomas',
                'gender' => 'Female',
                'date_of_birth' => '1991-11-14',
                'phone' => '9895123402',
                'alternative_phone' => '9895123412',
                'email' => 'meera.thomas@example.com',
                'qualification' => 'M.Sc Computer Science, B.Ed',
                'experience' => 7,
                'date_of_joining' => '2021-06-07',
                'department_id' => $departmentId('Computer Science'),
                'designation_id' => $designationId('Assistant Teacher'),
                'subject' => 'Computer Science',
                'class_in_charge_id' => $gradeId(4),
                'employment_type' => 'permanent',
                'salary' => 49000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 6),
                'name' => 'Suresh Babu',
                'gender' => 'Male',
                'date_of_birth' => '1984-02-26',
                'phone' => '9895123403',
                'alternative_phone' => null,
                'email' => 'suresh.babu@example.com',
                'qualification' => 'MA History, B.Ed',
                'experience' => 15,
                'date_of_joining' => '2017-05-22',
                'department_id' => $departmentId('Social Studies'),
                'designation_id' => $designationId('Head of Department'),
                'subject' => 'History',
                'class_in_charge_id' => $gradeId(5),
                'employment_type' => 'permanent',
                'salary' => 65000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 7),
                'name' => 'Lakshmi Krishnan',
                'gender' => 'Female',
                'date_of_birth' => '1994-08-09',
                'phone' => '9895123404',
                'alternative_phone' => '9895123414',
                'email' => 'lakshmi.krishnan@example.com',
                'qualification' => 'M.Sc Mathematics, B.Ed',
                'experience' => 5,
                'date_of_joining' => '2023-06-01',
                'department_id' => $departmentId('Mathematics'),
                'designation_id' => $designationId('Class Teacher'),
                'subject' => 'Mathematics',
                'class_in_charge_id' => $gradeId(6),
                'employment_type' => 'permanent',
                'salary' => 45000,
                'status' => 'active',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 8),
                'name' => 'Naveen Raj',
                'gender' => 'Male',
                'date_of_birth' => '1995-03-17',
                'phone' => '9895123405',
                'alternative_phone' => null,
                'email' => 'naveen.raj@example.com',
                'qualification' => 'MA English, B.Ed',
                'experience' => 4,
                'date_of_joining' => '2023-07-10',
                'department_id' => $departmentId('English'),
                'designation_id' => $designationId('Assistant Teacher'),
                'subject' => 'English',
                'class_in_charge_id' => $gradeId(7),
                'employment_type' => 'temporary',
                'salary' => 38000,
                'status' => 'active',
                'is_verified' => false,
            ],
            [
                'employee_id' => $codeService->format('teacher', 9),
                'name' => 'Fathima Ali',
                'gender' => 'Female',
                'date_of_birth' => '1989-12-03',
                'phone' => '9895123406',
                'alternative_phone' => '9895123416',
                'email' => 'fathima.ali@example.com',
                'qualification' => 'M.Sc Biology, B.Ed',
                'experience' => 9,
                'date_of_joining' => '2020-01-06',
                'department_id' => $departmentId('Science'),
                'designation_id' => $designationId('Senior Teacher'),
                'subject' => 'Biology',
                'class_in_charge_id' => $gradeId(8),
                'employment_type' => 'permanent',
                'salary' => 55000,
                'status' => 'on leave',
                'is_verified' => true,
            ],
            [
                'employee_id' => $codeService->format('teacher', 10),
                'name' => 'Vishnu Mohan',
                'gender' => 'Male',
                'date_of_birth' => '1992-06-21',
                'phone' => '9895123407',
                'alternative_phone' => null,
                'email' => 'vishnu.mohan@example.com',
                'qualification' => 'MCA, B.Ed',
                'experience' => 6,
                'date_of_joining' => '2022-04-04',
                'department_id' => $departmentId('Computer Science'),
                'designation_id' => $designationId('Class Teacher'),
                'subject' => 'Information Technology',
                'class_in_charge_id' => $gradeId(9),
                'employment_type' => 'permanent',
                'salary' => 48000,
                'status' => 'active',
                'is_verified' => true,
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
