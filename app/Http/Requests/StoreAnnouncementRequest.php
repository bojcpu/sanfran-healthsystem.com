<?php

namespace App\Http\Requests;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['required', Rule::enum(AnnouncementType::class)],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::enum(AnnouncementStatus::class)],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter an announcement title.',
            'content.required' => 'Please enter the announcement content.',
            'type.required' => 'Please select an announcement type.',
            'status.required' => 'Please select a status.',
            'publish_date.required' => 'Please choose a publish date.',
            'expiry_date.after_or_equal' => 'The expiry date must be on or after the publish date.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image may not be greater than 2MB.',
        ];
    }
}
