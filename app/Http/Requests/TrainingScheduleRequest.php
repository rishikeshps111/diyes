<?php

namespace App\Http\Requests;

use App\Models\TrainingSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'trainer_type_id' => ['required', 'integer', Rule::exists('trainer_types', 'id')],
            'trainer_category_id' => ['required', 'integer', Rule::exists('trainer_categories', 'id')],
            'conducted_by' => ['required', Rule::in(array_keys(TrainingSchedule::CONDUCTED_BY_OPTIONS))],
            'resource_person_trainer' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'per_day_hours' => ['required', 'numeric', 'gt:0', 'max:24'],
            'mode' => ['required', Rule::in(array_keys(TrainingSchedule::MODES))],
            'venue' => ['required', 'string', 'max:255'],
            'total_count' => ['required', 'integer', 'min:1'],
            'applicable' => ['required', Rule::in(array_keys(TrainingSchedule::APPLICABLE_OPTIONS))],
            'subject_ids' => ['exclude_unless:applicable,teachers', 'required_if:applicable,teachers', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'distinct', Rule::exists('subjects', 'id')],
            'training_objectives' => ['required', 'string'],
            'training_description' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(TrainingSchedule::STATUSES))],
            'sessions' => ['required', 'array', 'min:1'],
            'sessions.*.session_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:end_date'],
            'sessions.*.time_from' => ['required', 'date_format:H:i'],
            'sessions.*.time_to' => ['required', 'date_format:H:i', 'after:sessions.*.time_from'],
            'sessions.*.topic_module' => ['required', 'string', 'max:255'],
            'sessions.*.duration_hours' => ['required', 'numeric', 'gt:0', 'max:24'],
        ];
    }

    public function attributes(): array
    {
        return [
            'trainer_type_id' => 'type',
            'trainer_category_id' => 'category',
            'resource_person_trainer' => 'resource person / trainer',
            'per_day_hours' => 'per day hours',
            'subject_ids' => 'teaching staff subjects',
            'sessions.*.session_date' => 'session date',
            'sessions.*.time_from' => 'session start time',
            'sessions.*.time_to' => 'session end time',
            'sessions.*.topic_module' => 'topic module',
            'sessions.*.duration_hours' => 'session duration',
        ];
    }
}
