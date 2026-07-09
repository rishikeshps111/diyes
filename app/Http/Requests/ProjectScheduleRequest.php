<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $dateRules = ['required', 'date'];

        if ($project?->start_date) {
            $dateRules[] = 'after_or_equal:'.$project->start_date->format('Y-m-d');
        }

        if ($project?->end_date) {
            $dateRules[] = 'before_or_equal:'.$project->end_date->format('Y-m-d');
        }

        return [
            'schedule_date' => $dateRules,
            'topic' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        $project = $this->route('project');

        return [
            'schedule_date.after_or_equal' => 'Schedule date must be on or after '.$project?->start_date?->format('d M Y').'.',
            'schedule_date.before_or_equal' => 'Schedule date must be on or before '.$project?->end_date?->format('d M Y').'.',
        ];
    }
}
