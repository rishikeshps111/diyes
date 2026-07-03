<?php

namespace App\Http\Requests;

use App\Models\TeacherDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $document = $this->route('document');
        $fileRules = $document ? ['nullable'] : ['required'];

        return [
            'document_type' => [
                'required',
                'string',
                Rule::in(TeacherDocument::DOCUMENT_TYPES),
                Rule::unique('teacher_documents', 'document_type')
                    ->where('teacher_id', $teacher?->id)
                    ->ignore($document),
            ],
            'document_file' => [...$fileRules, 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.unique' => 'This document type is already added for this teacher.',
        ];
    }
}
