<?php

namespace App\Http\Requests;

use App\Models\ProjectWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectWeekRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'applicable_from' => [
                'required',
                'date',
                $this->isMethod('post') ? 'after_or_equal:today' : 'date',
            ],
            'applicable_to' => ['required', 'date', 'after:applicable_from'],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'grade_id' => ['required', 'integer', Rule::exists('grades', 'id')],
            'division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'total_periods' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys(ProjectWeek::STATUSES))],
        ];
    }
}
