<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRequest extends FormRequest
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
        $gradeId = $this->route('grade')?->id;

        return [
            'grade' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grades', 'grade')->ignore($gradeId),
            ],
            'capacity' => ['required', 'integer', 'min:1'],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
