<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public const GUARD = 'web';

    public function query(): Builder
    {
        return Role::query()
            ->with('permissions')
            ->withCount('users')
            ->where('name', '!=', 'admin');
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => self::GUARD,
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->forceFill([
            'name' => $data['name'],
            'guard_name' => self::GUARD,
        ])->save();

        $role->syncPermissions($data['permissions'] ?? []);

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function permissionGroups(): Collection
    {
        $permissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->get()
            ->keyBy('name');

        $groups = collect(self::orderedPermissionGroups())
            ->mapWithKeys(function (array $names, string $groupName) use ($permissions): array {
                return [
                    $groupName => collect($names)
                        ->map(fn(string $name) => $permissions->get($name))
                        ->filter()
                        ->values(),
                ];
            })
            ->filter(fn(Collection $groupPermissions): bool => $groupPermissions->isNotEmpty());

        $orderedNames = collect(self::orderedPermissionGroups())->flatten()->all();

        $extraPermissions = $permissions
            ->reject(fn(Permission $permission): bool => in_array($permission->name, $orderedNames, true))
            ->groupBy(fn(Permission $permission): string => $permission->group_name ?: 'Others')
            ->sortKeys();

        return $groups->merge($extraPermissions);
    }

    public static function orderedPermissionGroups(): array
    {
        return [
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
            'Subject' => [
                'view.subject',
                'create.subject',
                'edit.subject',
                'delete.subject',
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
            'Time Table Category' => [
                'view.time-table-category',
                'create.time-table-category',
                'edit.time-table-category',
                'delete.time-table-category',
            ],
            'Project Category' => [
                'view.project-category',
                'create.project-category',
                'edit.project-category',
                'delete.project-category',
            ],
            'Event Type' => [
                'view.event-type',
                'create.event-type',
                'edit.event-type',
                'delete.event-type',
            ],
            'Module Prefix' => [
                'view.module-prefix',
                'edit.module-prefix',
            ],
            'Teacher' => [
                'view.teacher',
                'create.teacher',
                'edit.teacher',
                'delete.teacher',
            ],
            'Project' => [
                'view.project',
                'create.project',
                'edit.project',
                'delete.project',
            ],
            'Project Week' => [
                'view.project-week',
                'create.project-week',
                'edit.project-week',
                'delete.project-week',
            ],
            'Special Event' => [
                'view.special-event',
                'create.special-event',
                'edit.special-event',
                'delete.special-event',
            ],
            'Regular Timetable' => [
                'view.timetable',
                'create.timetable',
                'edit.timetable',
                'delete.timetable',
            ],
            'User' => [
                'view.user',
                'create.user',
                'edit.user',
                'delete.user',
            ],
            'Role & Permission' => [
                'view.role',
                'create.role',
                'edit.role',
                'delete.role',
            ],
        ];
    }
}
