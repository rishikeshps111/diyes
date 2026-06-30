<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomRequest extends FormRequest
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
            'room_name' => ['required', 'string', 'max:255'],
            'building' => ['required', 'string', 'max:255'],
            'floor' => ['required', 'string', 'max:255'],
            'room_type' => ['required', 'string', Rule::in(Classroom::ROOM_TYPES)],
            'seating_capacity' => ['required', 'integer', 'min:1'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'equipment' => ['nullable', 'array'],
            'equipment.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
