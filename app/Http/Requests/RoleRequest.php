<?php

namespace App\Http\Requests;

use App\Services\RoleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'not_in:admin',
                Rule::unique('roles', 'name')
                    ->where('guard_name', RoleService::GUARD)
                    ->ignore($roleId),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where('guard_name', RoleService::GUARD),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => 'The admin role is protected.',
        ];
    }
}
