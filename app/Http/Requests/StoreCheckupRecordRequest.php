<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckupRecordRequest extends FormRequest
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
            'visit_date' => ['required', 'date'],
            'complaint' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'prescription' => ['nullable', 'string'],
            'attending_personnel' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:visit_date'],
        ];
    }
}
