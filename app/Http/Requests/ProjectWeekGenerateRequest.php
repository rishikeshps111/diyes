<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectWeekGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.timetable_entry_id' => ['required', 'integer', Rule::exists('timetable_entries', 'id')],
            'entries.*.teacher_ids' => ['required', 'array', 'min:1', 'max:2'],
            'entries.*.teacher_ids.*' => ['integer', 'distinct', Rule::exists('teachers', 'id')],
        ];
    }
}
