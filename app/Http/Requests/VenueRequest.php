<?php

namespace App\Http\Requests;

use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VenueRequest extends FormRequest
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
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_type' => ['required', 'string', Rule::in(Venue::VENUE_TYPES)],
            'building' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
