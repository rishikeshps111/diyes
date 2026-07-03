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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
