<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'entries.*.teacher_ids.*' => ['integer', Rule::exists('teachers', 'id')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('entries', []) as $index => $entry) {
                $teacherIds = array_filter($entry['teacher_ids'] ?? []);

                if (count($teacherIds) !== count(array_unique($teacherIds))) {
                    $validator->errors()->add("entries.{$index}.teacher_ids", 'Select different teachers for the same project period.');
                }
            }
        });
    }
}
