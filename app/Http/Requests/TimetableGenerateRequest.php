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
            'entries.*.subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'entries.*.teacher_ids' => ['required', 'array', 'min:1', 'max:2'],
            'entries.*.teacher_ids.*' => ['required', 'integer', Rule::exists('teachers', 'id')],
            'entries.*.start_time' => ['required', 'date_format:H:i'],
            'entries.*.end_time' => ['required', 'date_format:H:i'],
            'short_break_after_period' => ['required', 'integer', 'min:1'],
            'lunch_break_after_period' => ['required', 'integer', 'min:1', 'different:short_break_after_period'],
            'short_break_after_lunch_period' => ['required', 'integer', 'min:1', 'different:short_break_after_period', 'different:lunch_break_after_period'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $timetable = $this->route('timetable');
            $totalPeriods = max(1, (int) ($timetable?->total_periods_per_day ?? 1));
            $selectedSlots = [];

            foreach (['short_break_after_period', 'lunch_break_after_period', 'short_break_after_lunch_period'] as $field) {
                $period = (int) $this->input($field);

                if ($period > $totalPeriods) {
                    $validator->errors()->add($field, 'The selected period must be within the timetable total periods.');
                }
            }

            foreach ($this->input('entries', []) as $index => $entry) {
                $periodNo = (int) ($entry['period_no'] ?? 0);
                $teacherIds = array_filter($entry['teacher_ids'] ?? []);

                if (count($teacherIds) !== count(array_unique($teacherIds))) {
                    $validator->errors()->add("entries.{$index}.teacher_ids", 'Select different teachers for the same period.');
                }

                if ($periodNo > $totalPeriods) {
                    $validator->errors()->add("entries.{$index}.period_no", 'The selected period must be within the timetable total periods.');
                }

                $slot = ($entry['day'] ?? '').'|'.$periodNo;

                if (isset($selectedSlots[$slot])) {
                    $validator->errors()->add("entries.{$index}.period_no", 'This day and period is already added.');
                }

                $selectedSlots[$slot] = true;

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

            foreach (['short_break_after_period', 'lunch_break_after_period', 'short_break_after_lunch_period'] as $field) {
                $period = (int) $this->input($field);
                $days = collect($this->input('entries', []))->pluck('day')->filter()->unique();

                foreach ($days as $day) {
                    if ($period && ! isset($selectedSlots[$day.'|'.$period])) {
                        $validator->errors()->add($field, "Add Period {$period} details for {$day} before selecting this break position.");
                    }
                }
            }
        });
    }
}
