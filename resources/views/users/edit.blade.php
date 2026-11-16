@extends('layouts.app')

@section('content')
    <x-page-header title="Edit User" :description="'Update account for ' . $user->name . '.'">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn-secondary">Back to Users</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Full Name" name="name" :value="$user->name" required />
                <x-form.input label="Username" name="username" :value="$user->username" required />
                <x-form.input label="Email" name="email" type="email" :value="$user->email" required />
                <x-form.select
                    label="Role"
                    name="role"
                    :options="collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()"
                    :value="$user->role->value"
                    required
                />
                <x-form.input label="New Password" name="password" type="password" hint="Leave blank to keep current password. If changing it, use at least 8 characters with a letter, number, and special character." />
                <x-form.input label="Confirm Password" name="password_confirmation" type="password" />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-gray-300 text-primary focus:ring-primary/30">
                <label for="is_active" class="text-sm text-gray-700">Active account</label>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Update User</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
