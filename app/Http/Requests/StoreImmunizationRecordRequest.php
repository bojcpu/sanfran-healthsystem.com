<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImmunizationRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose' => ['nullable', 'string', 'max:100'],
            'vaccination_date' => ['required', 'date'],
            'next_schedule' => ['nullable', 'date', 'after_or_equal:vaccination_date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
