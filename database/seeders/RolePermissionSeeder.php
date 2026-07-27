<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\RoleService;
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
        $permissionGroups = RoleService::orderedPermissionGroups();

        $permissionNames = [];

        foreach ($permissionGroups as $groupName => $permissions) {
            foreach ($permissions as $name) {
                $permission = Permission::findOrCreate($name, $guard);
                $permission->forceFill(['group_name' => $groupName])->save();

                $permissionNames[] = $name;
            }
        }

        Permission::query()
            ->where('guard_name', $guard)
            ->where('name', 'delete.module-prefix')
            ->delete();

        $admin = Role::findOrCreate('admin', $guard);
        $admin->syncPermissions(
            Permission::query()
                ->whereIn('name', $permissionNames)
                ->where('guard_name', $guard)
                ->get()
        );

        $staff = Role::findOrCreate('staff', $guard);
        $staff->syncPermissions(['dashboard.view']);

        $teacher = Role::findOrCreate('Teacher', $guard);
        $teacher->syncPermissions(['dashboard.view']);

        $academicSupervisor = Role::findOrCreate('Academic Supervisor', $guard);
        $academicSupervisor->syncPermissions([
            'dashboard.view',
            'view.academic-year',
            'create.academic-year',
            'edit.academic-year',
            'view.grade',
            'create.grade',
            'edit.grade',
            'view.subject',
            'create.subject',
            'edit.subject',
            'view.division',
            'create.division',
            'edit.division',
            'view.holiday',
            'create.holiday',
            'edit.holiday',
            'view.leave-type',
            'create.leave-type',
            'edit.leave-type',
            'delete.leave-type',
            'view.leave-application',
            'create.leave-application',
            'edit.leave-application',
            'approve.leave-application',
            'view.time-table-category',
            'create.time-table-category',
            'edit.time-table-category',
            'view.project-category',
            'create.project-category',
            'edit.project-category',
            'view.event-type',
            'create.event-type',
            'edit.event-type',
            'view.trainer-type',
            'create.trainer-type',
            'edit.trainer-type',
            'view.trainer-category',
            'create.trainer-category',
            'edit.trainer-category',
            'view.timetable',
            'create.timetable',
            'edit.timetable',
            'delete.timetable',
            'view.project',
            'create.project',
            'edit.project',
            'delete.project',
            'view.project-week',
            'create.project-week',
            'edit.project-week',
            'delete.project-week',
            'view.training-schedule',
            'create.training-schedule',
            'edit.training-schedule',
            'delete.training-schedule',
            'view.special-event',
            'create.special-event',
            'edit.special-event',
            'delete.special-event',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
