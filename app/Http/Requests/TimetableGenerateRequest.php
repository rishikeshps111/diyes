<?php

namespace App\Http\Requests;

use App\Models\TimetableEntry;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TimetableGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.day' => ['required', 'string', Rule::in(TimetableEntry::DAYS)],
            'entries.*.period_no' => ['required', 'integer', 'min:1'],
            'entries.*.entry_type' => ['required', 'string', Rule::in(array_keys(TimetableEntry::TYPES))],
            'entries.*.subject_id' => ['nullable', 'integer', Rule::exists('subjects', 'id')],
            'entries.*.teacher_1_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
            'entries.*.teacher_2_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
            'entries.*.start_time' => ['required', 'date_format:H:i'],
            'entries.*.end_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('entries', []) as $index => $entry) {
                if (($entry['teacher_1_id'] ?? null) && ($entry['teacher_1_id'] ?? null) === ($entry['teacher_2_id'] ?? null)) {
                    $validator->errors()->add("entries.{$index}.teacher_2_id", 'Teacher 2 must be different from Teacher 1.');
                }

                if (empty($entry['start_time']) || empty($entry['end_time'])) {
                    continue;
                }

                try {
                    $start = Carbon::createFromFormat('H:i', $entry['start_time']);
                    $end = Carbon::createFromFormat('H:i', $entry['end_time']);
                } catch (\Throwable) {
                    continue;
                }

                if ($end->lessThanOrEqualTo($start)) {
                    $validator->errors()->add("entries.{$index}.end_time", 'End time must be after start time.');
                }
            }
        });
    }
}
