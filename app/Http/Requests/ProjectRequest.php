<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'project_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('projects', 'project_code')->ignore($projectId),
            ],
            'project_title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'project_category_id' => ['required', 'integer', Rule::exists('project_categories', 'id')],
            'duration_days' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'distinct', Rule::exists('subjects', 'id')],
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['integer', 'distinct', Rule::exists('grades', 'id')],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['integer', 'distinct', Rule::exists('teachers', 'id')->where('status', 'active')],
            'venue' => ['nullable', 'string', 'max:255'],
            'timetable_replacement' => ['required', 'boolean'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
        ];
    }
}
