<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the base roles and permissions used by the admin theme.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';
        $permissionGroups = [
            'Dashboard' => [
                'dashboard.view',
            ],
            'Academic Year' => [
                'view.academic-year',
                'create.academic-year',
                'edit.academic-year',
                'delete.academic-year',
            ],
            'Grade' => [
                'view.grade',
                'create.grade',
                'edit.grade',
                'delete.grade',
            ],
            'Division' => [
                'view.division',
                'create.division',
                'edit.division',
                'delete.division',
            ],
            'Department' => [
                'view.department',
                'create.department',
                'edit.department',
                'delete.department',
            ],
            'Designation' => [
                'view.designation',
                'create.designation',
                'edit.designation',
                'delete.designation',
            ],
            'Classroom' => [
                'view.classroom',
                'create.classroom',
                'edit.classroom',
                'delete.classroom',
            ],
            'Venue' => [
                'view.venue',
                'create.venue',
                'edit.venue',
                'delete.venue',
            ],
            'Holiday' => [
                'view.holiday',
                'create.holiday',
                'edit.holiday',
                'delete.holiday',
            ],
        ];

        $permissionNames = [];

        foreach ($permissionGroups as $groupName => $permissions) {
            foreach ($permissions as $name) {
                $permission = Permission::findOrCreate($name, $guard);
                $permission->forceFill(['group_name' => $groupName])->save();

                $permissionNames[] = $name;
            }
        }

        $admin = Role::findOrCreate('admin', $guard);
        $admin->syncPermissions(
            Permission::query()
                ->whereIn('name', $permissionNames)
                ->where('guard_name', $guard)
                ->get()
        );

        $staff = Role::findOrCreate('staff', $guard);
        $staff->syncPermissions(['dashboard.view']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
