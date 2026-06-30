<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
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
            'holiday_name' => ['required', 'string', 'max:255'],
            'holiday_type' => ['required', 'string', Rule::in(Holiday::HOLIDAY_TYPES)],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'holiday_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'applicable_branch' => ['nullable', 'string', 'max:255'],
            'applicable_classes' => ['nullable', 'string', Rule::in(Holiday::APPLICABLE_CLASSES)],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
