<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DesignationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'designation_name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'grade_id' => ['required', 'integer', Rule::exists('grades', 'id')],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
