<?php

namespace App\Http\Requests;

use App\Models\Timetable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timetable_name' => ['required', 'string', 'max:255'],
            'timetable_category_id' => ['required', 'integer', Rule::exists('time_table_categories', 'id')],
            'applicable_from' => [
                'required',
                'date',
                $this->isMethod('post') ? 'after_or_equal:today' : 'date',
            ],
            'applicable_to' => ['required', 'date', 'after:applicable_from'],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'grade_id' => ['required', 'integer', Rule::exists('grades', 'id')],
            'division_id' => ['required', 'integer', Rule::exists('divisions', 'id')],
            'total_periods_per_day' => ['required', 'integer', 'min:1'],
            'period_duration_minutes' => ['required', 'integer', 'min:1'],
            'short_break_minutes' => ['required', 'integer', 'min:0'],
            'lunch_break_minutes' => ['required', 'integer', 'min:0'],
            'short_break_after_lunch_minutes' => ['required', 'integer', 'min:0'],
            'timetable_incharge_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('id', function ($query) {
                        $query->select('model_id')
                            ->from('model_has_roles')
                            ->where('model_type', 'App\\Models\\User')
                            ->whereIn('role_id', function ($query) {
                                $query->select('id')
                                    ->from('roles')
                                    ->where('name', 'Academic Supervisor')
                                    ->where('guard_name', 'web');
                            });
                    });
                }),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(array_keys(Timetable::STATUSES))],
        ];
    }
}
