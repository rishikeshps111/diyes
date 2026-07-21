<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $teacherSubject = $this->route('teacherSubject');

        return [
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                Rule::unique('teacher_subjects', 'subject_id')
                    ->where('teacher_id', $teacher?->id)
                    ->where('grade_id', $this->integer('grade_id'))
                    ->ignore($teacherSubject),
            ],
        ];
    }

    public function messages(): array
    {
        return ['subject_id.unique' => 'This subject is already assigned to this teacher.'];
    }
}
