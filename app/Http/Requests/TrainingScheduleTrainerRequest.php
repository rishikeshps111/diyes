<?php

namespace App\Http\Requests;

use App\Models\TrainingScheduleTrainer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingScheduleTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $trainingSchedule = $this->route('training_schedule');
        $assignment = $this->route('trainer');

        return [
            'designation_id' => ['required', 'integer', Rule::exists('designations', 'id')],
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')->where(
                    fn ($query) => $query->where('designation_id', $this->input('designation_id')),
                ),
            ],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id'),
                Rule::unique('training_schedule_trainers', 'subject_id')
                    ->where(fn ($query) => $query
                        ->where('training_schedule_id', $trainingSchedule?->id)
                        ->where('teacher_id', $this->input('teacher_id')))
                    ->ignore($assignment instanceof TrainingScheduleTrainer ? $assignment->id : null),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'designation_id' => 'designation',
            'teacher_id' => 'name',
            'subject_id' => 'subject',
        ];
    }
}
