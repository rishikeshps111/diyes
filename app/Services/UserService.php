<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(private readonly PrefixCodeService $prefixCodeService) {}

    public function query(array $filters = []): Builder
    {
        return User::query()
            ->with(['department', 'role'])
            ->whereDoesntHave('teacher')
            ->where(function (Builder $query): void {
                $query->whereDoesntHave('role', fn (Builder $query) => $query->where('name', 'admin'))
                    ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'admin'));
            })
            ->when($filters['department_id'] ?? null, fn (Builder $query, string $id) => $query->where('department_id', $id))
            ->when($filters['role_id'] ?? null, fn (Builder $query, string $id) => $query->where('role_id', $id))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn (Builder $query) => $query->where('is_active', (bool) $filters['is_active']))
            ->when($filters['last_login_at'] ?? null, fn (Builder $query, string $date) => $query->whereDate('last_login_at', $date));
    }

    public function selectedForExport(array $ids): Collection
    {
        return User::query()
            ->with(['department', 'role'])
            ->whereDoesntHave('teacher')
            ->where(function (Builder $query): void {
                $query->whereDoesntHave('role', fn (Builder $query) => $query->where('name', 'admin'))
                    ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'admin'));
            })
            ->whereKey($ids)
            ->orderByDesc('created_at')
            ->get();
    }

    public function nextEmployeeCode(): string
    {
        $code = $this->prefixCodeService->next('user', User::class, 'employee_code');

        while (
            User::query()->where('employee_code', $code)->exists()
            || Teacher::query()->where('employee_id', $code)->exists()
        ) {
            preg_match('/^(.*?)(\d+)$/', $code, $matches);
            $number = ((int) ($matches[2] ?? 0)) + 1;
            $code = ($matches[1] ?? $code).str_pad((string) $number, strlen($matches[2] ?? '0000'), '0', STR_PAD_LEFT);
        }

        return $code;
    }

    public function create(array $data): User
    {
        $image = $data['profile_image'] ?? null;
        unset($data['profile_image']);

        $user = User::create([
            ...$this->userPayload($data),
            'employee_code' => $this->nextEmployeeCode(),
            'password' => $data['password'],
            'profile_image' => $image instanceof UploadedFile ? $image->store('user-profile-images', 'public') : null,
        ]);

        $this->syncRole($user, (int) $data['role_id']);

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $image = $data['profile_image'] ?? null;
        unset($data['profile_image']);

        $payload = $this->userPayload($data);

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        if ($image instanceof UploadedFile) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $payload['profile_image'] = $image->store('user-profile-images', 'public');
        }

        $user->update($payload);
        $this->syncRole($user, (int) $data['role_id']);

        return $user;
    }

    public function delete(User $user): void
    {
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return $user;
    }

    public function resetPassword(User $user, string $password): User
    {
        $user->forceFill(['password' => $password])->save();

        return $user;
    }

    public function departments(): Collection
    {
        return Department::query()->orderBy('department_name')->get(['id', 'department_name']);
    }

    public function designations(): Collection
    {
        return Designation::query()->orderBy('designation_name')->get(['id', 'designation_name']);
    }

    public function roles(): Collection
    {
        return Role::query()
            ->where('guard_name', RoleService::GUARD)
            ->whereNotIn('name', ['admin', 'Teacher'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function userPayload(array $data): array
    {
        return Arr::only($data, [
            'username',
            'name',
            'email',
            'phone_country_code',
            'phone',
            'department_id',
            'designation_id',
            'role_id',
            'is_active',
            'is_two_factor_enabled',
            'remarks',
        ]);
    }

    private function syncRole(User $user, int $roleId): void
    {
        $role = Role::query()->find($roleId);

        if ($role) {
            $user->syncRoles([$role]);
        }
    }
}
