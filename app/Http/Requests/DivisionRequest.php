<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DivisionRequest extends FormRequest
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
            'division' => ['required', 'string', 'max:255'],
            'grade_id' => ['required', 'integer', Rule::exists('grades', 'id')],
            'capacity' => ['required', 'integer', 'min:1'],
            'class_teacher' => ['nullable', 'string', 'max:255'],
            'room_number' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
