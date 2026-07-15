<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubstituteAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')->where('status', 'active')],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.timetable_entry_id' => ['nullable', 'integer', Rule::exists('timetable_entries', 'id')],
            'allocations.*.grade_id' => ['required', 'integer', Rule::exists('grades', 'id')],
            'allocations.*.division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'allocations.*.period_no' => ['required', 'integer', 'min:1'],
            'allocations.*.allocation_date' => ['required', 'date'],
            'allocations.*.substitute_teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')->where('status', 'active')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $schedule = $this->route('training_schedule');
            $teacher = Teacher::find($this->integer('teacher_id'));
            if (! $teacher || ! $schedule) {
                $validator->errors()->add('teacher_id', 'Select a valid teacher.');
                return;
            }

            foreach ($this->input('allocations', []) as $index => $allocation) {
                $date = isset($allocation['allocation_date']) ? now()->parse($allocation['allocation_date']) : null;
                $substituteId = (int) ($allocation['substitute_teacher_id'] ?? 0);

                if ($date && ($date->lt($schedule->start_date) || $date->gt($schedule->end_date))) {
                    $validator->errors()->add("allocations.{$index}.allocation_date", 'The allocation date must be within the training dates.');
                }
                if ($substituteId === $teacher->id) {
                    $validator->errors()->add("allocations.{$index}.substitute_teacher_id", 'The selected teacher cannot substitute for themselves.');
                }
                if (Teacher::query()->whereKey($substituteId)->doesntExist()) {
                    $validator->errors()->add("allocations.{$index}.substitute_teacher_id", 'Select a valid substitute teacher.');
                }
            }
        }];
    }
}
