<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'teacher_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(Teacher::GENDERS)],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'phone_country_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'alternative_phone_country_code' => ['nullable', 'string', 'max:10'],
            'alternative_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('teachers', 'email')->ignore($teacher)],
            'qualification' => ['required', 'string', 'max:255'],
            'experience' => ['required', 'integer', 'min:0'],
            'date_of_joining' => ['required', 'date'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'designation_id' => ['required', 'integer', Rule::exists('designations', 'id')],
            'subject' => ['required', 'string', 'max:255'],
            'class_in_charge_id' => ['nullable', 'integer', Rule::exists('grades', 'id')],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')],
            'address' => ['required', 'string'],
            'pincode' => ['required', 'string', 'max:10'],
            'employment_type' => ['required', 'string', Rule::in(Teacher::EMPLOYMENT_TYPES)],
            'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(Teacher::STATUSES)],
        ];
    }
}
