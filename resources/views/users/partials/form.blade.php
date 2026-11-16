@php
    $editingUser = $editingUser ?? null;
    $formModalName = $modalName ?? 'add-user';
@endphp

<form method="POST" action="{{ $editingUser ? route('users.update', $editingUser) : route('users.store') }}" class="space-y-5">
    @csrf
    @if ($editingUser)
        @method('PUT')
    @endif
    <input type="hidden" name="_modal" value="{{ $formModalName }}">

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.input label="Full Name" name="name" :value="$editingUser?->name" id="user-name-{{ $editingUser?->id ?? 'new' }}" required />
        <x-form.input label="Username" name="username" :value="$editingUser?->username" id="user-username-{{ $editingUser?->id ?? 'new' }}" required />
        <x-form.input label="Email" name="email" type="email" :value="$editingUser?->email" id="user-email-{{ $editingUser?->id ?? 'new' }}" required />
        <x-form.select
            label="Role"
            name="role"
            :options="collect($roles)->mapWithKeys(fn ($r) => [$r->value => $r->label()])->all()"
            :value="$editingUser?->role->value"
            id="user-role-{{ $editingUser?->id ?? 'new' }}"
            placeholder="Select role"
            required
        />
        <x-form.input label="{{ $editingUser ? 'New Password' : 'Password' }}" name="password" type="password" :hint="$editingUser ? 'Leave blank to keep current password. If changing it, use at least 8 characters with a letter, number, and special character.' : 'Use at least 8 characters with a letter, number, and special character.'" id="user-password-{{ $editingUser?->id ?? 'new' }}" :required="! $editingUser" />
        <x-form.input label="Confirm Password" name="password_confirmation" type="password" id="user-password-confirmation-{{ $editingUser?->id ?? 'new' }}" :required="! $editingUser" />
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="user-is-active-{{ $editingUser?->id ?? 'new' }}" value="1" @checked(old('is_active', $editingUser?->is_active ?? true)) class="rounded border-gray-300 text-primary focus:ring-primary/30">
        <label for="user-is-active-{{ $editingUser?->id ?? 'new' }}" class="text-sm text-gray-700">Active account</label>
    </div>

    <div class="flex gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="btn-primary">{{ $editingUser ? 'Update User' : 'Create User' }}</button>
        <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
    </div>
</form>
