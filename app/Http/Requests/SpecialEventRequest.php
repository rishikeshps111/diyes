<?php

namespace App\Http\Requests;

use App\Models\SpecialEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_title' => ['required', 'string', 'max:255'],
            'event_type_id' => ['required', 'integer', Rule::exists('event_types', 'id')],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'event_start_date' => ['required', 'date', 'after_or_equal:today'],
            'event_end_date' => ['required', 'date', 'after_or_equal:event_start_date'],
            'media_coverable' => ['required', 'boolean'],
            'timings' => ['required', 'array', 'min:1'],
            'timings.*.day_number' => ['required', 'integer', 'min:1'],
            'timings.*.event_date' => ['required', 'date'],
            'timings.*.day_label' => ['required', 'string', 'max:50'],
            'timings.*.start_time' => ['required', 'date_format:H:i'],
            'timings.*.end_time' => ['required', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'organized_by' => ['nullable', 'string', 'max:255'],
            'staff_coordinator_ids' => ['nullable', 'array'],
            'staff_coordinator_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
            'teacher_coordinator_ids' => ['nullable', 'array'],
            'teacher_coordinator_ids.*' => ['integer', 'distinct', Rule::exists('teachers', 'id')],
            'incharge' => ['nullable', 'string', 'max:255'],
            'contact_no' => ['nullable', 'string', 'max:30'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['string', Rule::in(array_keys(SpecialEvent::PARTICIPANTS))],
            'grade_ids' => ['nullable', 'array'],
            'grade_ids.*' => ['integer', 'distinct', Rule::exists('grades', 'id')],
            'division_ids' => ['nullable', 'array'],
            'division_ids.*' => ['integer', 'distinct', Rule::exists('divisions', 'id')],
            'outside_candidates' => ['required', 'boolean'],
            'objective' => ['nullable', 'string', 'max:255'],
            'event_details' => ['nullable', 'string'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'status' => ['required', 'string', Rule::in(array_keys(SpecialEvent::STATUSES))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $staff = collect($this->input('staff_coordinator_ids', []))->filter();
            $teachers = collect($this->input('teacher_coordinator_ids', []))->filter();

            if ($staff->isEmpty() && $teachers->isEmpty()) {
                $validator->errors()->add('staff_coordinator_ids', 'Select at least one event coordinator.');
            }

            foreach ($this->input('timings', []) as $index => $timing) {
                if (($timing['start_time'] ?? null) && ($timing['end_time'] ?? null) && $timing['end_time'] <= $timing['start_time']) {
                    $validator->errors()->add("timings.$index.end_time", 'The end time must be after the start time.');
                }
            }
        });
    }
}
