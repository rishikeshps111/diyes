<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $applicableFor = (string) $this->input('applicable_for');

        if (str_starts_with($applicableFor, 'role:')) {
            $this->merge([
                'applicable_for' => 'role',
                'role_id' => (int) str($applicableFor)->after('role:')->toString(),
            ]);
        } else {
            $this->merge(['role_id' => null]);
        }
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type')?->id;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('leave_types', 'code')->ignore($leaveTypeId)],
            'leave_name' => ['required', 'string', 'max:100', Rule::unique('leave_types', 'leave_name')->ignore($leaveTypeId)],
            'leave_type' => ['required', Rule::in(array_keys(LeaveType::LEAVE_TYPES))],
            'max_leaves_per_year' => ['required', 'integer', 'min:0'],
            'carry_forward_allowed' => ['required', 'boolean'],
            'max_carry_forward_limit' => ['required_if:carry_forward_allowed,1', 'nullable', 'integer', 'min:0'],
            'applicable_for' => ['required', Rule::in(array_keys(LeaveType::APPLICABLE_FOR))],
            'role_id' => ['required_if:applicable_for,role', 'nullable', 'integer', Rule::exists('roles', 'id')],
            'gender_specific' => ['required', Rule::in(array_keys(LeaveType::GENDERS))],
            'max_leave_days_per_request' => ['required', 'integer', 'min:1'],
            'advance_notice_days' => ['required', 'integer', 'min:0'],
            'allow_half_day' => ['required', 'boolean'],
            'requires_approval' => ['required', 'boolean'],
            'encashment_allowed' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
