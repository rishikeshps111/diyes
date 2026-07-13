<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialEventGenerateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'grade_ids' => ['required', 'array', 'min:1'],
            'grade_ids.*' => ['integer', 'distinct', Rule::exists('grades', 'id')],
            'division_ids' => ['required', 'array', 'min:1'],
            'division_ids.*' => ['integer', 'distinct', Rule::exists('divisions', 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.day' => ['required', Rule::in(\App\Models\TimetableEntry::DAYS)],
            'entries.*.period_no' => ['required', 'integer', 'min:1'],
        ];
    }
}
