@extends('layouts.app')

@section('content')
    <x-page-header title="Add User" description="Create a new system user account.">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn-secondary">Back to Users</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Full Name" name="name" required />
                <x-form.input label="Username" name="username" required />
                <x-form.input label="Email" name="email" type="email" required />
                <x-form.select
                    label="Role"
                    name="role"
                    :options="collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()"
                    placeholder="Select role"
                    required
                />
                <x-form.input label="Password" name="password" type="password" hint="Use at least 8 characters with a letter, number, and special character." required />
                <x-form.input label="Confirm Password" name="password_confirmation" type="password" required />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 text-primary focus:ring-primary/30">
                <label for="is_active" class="text-sm text-gray-700">Active account</label>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Create User</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
