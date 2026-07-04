<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use App\Services\PrefixCodeService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Seed non-admin users.
     */
    public function run(): void
    {
        $role = Role::query()
            ->where('name', 'Academic Supervisor')
            ->where('guard_name', 'web')
            ->first();

        $departmentId = Department::query()->orderBy('id')->value('id');
        $designationId = Designation::query()->orderBy('id')->value('id');
        $codeService = app(PrefixCodeService::class);

        $users = [
            [
                'employee_code' => $codeService->format('user', 2),
                'username' => 'academic.supervisor1',
                'name' => 'Academic Supervisor 1',
                'email' => 'academic.supervisor1@gmail.com',
                'phone' => '9876543210',
            ],
            [
                'employee_code' => $codeService->format('user', 3),
                'username' => 'academic.supervisor2',
                'name' => 'Academic Supervisor 2',
                'email' => 'academic.supervisor2@gmail.com',
                'phone' => '9876543211',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    ...$userData,
                    'phone_country_code' => '+91',
                    'department_id' => $departmentId,
                    'designation_id' => $designationId,
                    'role_id' => $role?->id,
                    'password' => 'password@123',
                    'is_active' => true,
                    'is_two_factor_enabled' => false,
                ],
            );

            if ($role) {
                $user->syncRoles([$role]);
            }
        }
    }
}
