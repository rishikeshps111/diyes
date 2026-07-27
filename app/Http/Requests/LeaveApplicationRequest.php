<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->input('user_type');
        $isTeacher = $type === 'teacher';

        $this->merge([
            'applicant_type' => $isTeacher ? 'teacher' : 'user',
            'role_id' => $isTeacher ? null : (int) str($type)->after('role:')->toString(),
            'teacher_id' => $isTeacher ? $this->input('applicant_id') : null,
            'user_id' => $isTeacher ? null : $this->input('applicant_id'),
            'is_half_day' => $this->boolean('is_half_day'),
        ]);
    }

    public function rules(): array
    {
        return [
            'user_type' => ['required', 'string'],
            'applicant_type' => ['required', Rule::in(['teacher', 'user'])],
            'role_id' => ['nullable', 'required_if:applicant_type,user', 'integer', Rule::exists('roles', 'id')],
            'teacher_id' => ['nullable', 'required_if:applicant_type,teacher', 'integer', Rule::exists('teachers', 'id')],
            'user_id' => ['nullable', 'required_if:applicant_type,user', 'integer', Rule::exists('users', 'id')],
            'leave_type_id' => ['required', 'integer', Rule::exists('leave_types', 'id')->where('status', true)],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'is_half_day' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
