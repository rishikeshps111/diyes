<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModulePrefixRequest extends FormRequest
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
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'prefix' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
        ];
    }
}
