<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $this->user()?->canManageUsers()
            && $user instanceof \App\Models\User
            && $this->user()->role->canManageUser($user->role);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $assignableRoles = collect($this->user()?->assignableRoles() ?? [])
            ->map(fn (UserRole $role) => $role->value)
            ->all();

        $targetUser = $this->route('user');

        if ($targetUser instanceof \App\Models\User && $targetUser->role === UserRole::SystemAdmin) {
            $assignableRoles[] = UserRole::SystemAdmin->value;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->route('user'))],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', Password::min(8)->letters()->numbers()->symbols(), 'confirmed'],
            'role' => ['required', Rule::in($assignableRoles)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'You are not allowed to assign the selected role.',
        ];
    }
}
