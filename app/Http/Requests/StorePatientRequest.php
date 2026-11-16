<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $purok = $this->input('purok');

        if (! filled($purok)) {
            return;
        }

        $address = trim((string) $this->input('address', ''));
        $address = preg_replace('/^Purok [1-7]\s*,?\s*/i', '', $address);
        $address = filled($address) ? $address : 'San Francisco, Talibon, Bohol';

        $this->merge([
            'address' => "{$purok}, {$address}",
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'in:Male,Female'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'contact_number' => ['required', 'digits:11'],
            'email' => ['nullable', 'email', 'max:255'],
            'purok' => ['required', Rule::in(array_map(fn ($number) => "Purok {$number}", range(1, 7)))],
            'address' => ['required', 'string'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'emergency_contact_person' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_number' => ['nullable', 'digits:11'],
            'allergies' => ['nullable', 'string'],
            'existing_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
