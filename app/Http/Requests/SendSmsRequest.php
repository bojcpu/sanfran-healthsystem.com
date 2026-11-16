<?php

namespace App\Http\Requests;

use App\Enums\SmsNotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(
            \App\Enums\UserRole::SystemAdmin,
            \App\Enums\UserRole::Midwife,
        ) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'mobile_number' => ['required', 'string', 'max:15'],
            'message' => ['required', 'string', 'max:500'],
            'notification_type' => ['required', Rule::enum(SmsNotificationType::class)],
            'return_to' => ['nullable', Rule::in(['checkups.index', 'immunizations.index', 'maternal-care.index'])],
        ];
    }
}
