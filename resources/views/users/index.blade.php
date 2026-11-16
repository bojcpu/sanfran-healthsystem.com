@extends('layouts.app')

@section('content')
    <x-page-header title="Users" description="{{ $canManageUsers ? 'Manage system users and roles.' : 'View system users and roles.' }}">
        @if ($canManageUsers)
            <x-slot:actions>
                <button type="button" class="btn-primary" @click="$store.modal.open('add-user')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add User
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="users" :value="request('search')" placeholder="Search by name, username, or email..." />
            <select name="role" class="form-select sm:w-48">
                <option value="">All Roles</option>
                @foreach ($roles as $roleOption)
                    <option value="{{ $roleOption->value }}" @selected(request('role') === $roleOption->value)>{{ $roleOption->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary sm:self-end">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Name</th>
                        <th class="table-header table-cell">Username</th>
                        <th class="table-header table-cell">Email</th>
                        <th class="table-header table-cell">Role</th>
                        <th class="table-header table-cell">Status</th>
                        <th class="table-header table-cell">Last Login</th>
                        @if ($canManageUsers)
                            <th class="table-header table-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($users as $user)
                        <tr>
                            <td class="table-cell font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="table-cell">{{ $user->username }}</td>
                            <td class="table-cell">{{ $user->email }}</td>
                            <td class="table-cell"><span class="badge-primary">{{ $user->role->label() }}</span></td>
                            <td class="table-cell">
                                @if ($user->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $user->last_login?->format('M d, Y h:i A') ?? '—' }}</td>
                            @if ($canManageUsers)
                                <td class="table-cell">
                                    @if (auth()->user()->role->canManageUser($user->role))
                                        <div class="table-actions">
                                            <button type="button" class="btn-secondary text-xs" @click="$store.modal.open('edit-user-{{ $user->id }}')">Edit</button>
                                            @if ($user->id !== auth()->id() && $user->is_active)
                                                <form method="POST" action="{{ route('users.deactivate', $user) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="button" data-confirm-deactivate="Deactivate {{ $user->name }}?" class="btn-primary text-xs">Inactive</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageUsers ? 7 : 6 }}" class="px-4 py-12 text-center text-sm text-gray-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>

    @if ($canManageUsers)
        <x-modal name="add-user" title="Add User" description="Create a new system user account." maxWidth="3xl">
            @include('users.partials.form')
        </x-modal>

        @foreach ($users as $user)
            @php
                $modalName = 'edit-user-' . $user->id;
                $editRoles = $user->role === \App\Enums\UserRole::SystemAdmin
                    ? [\App\Enums\UserRole::SystemAdmin, ...$roles]
                    : $roles;
            @endphp
            <x-modal :name="$modalName" title="Edit User" :description="'Update account for ' . $user->name . '.'" maxWidth="3xl">
                @include('users.partials.form', ['editingUser' => $user, 'roles' => $editRoles, 'modalName' => $modalName])
            </x-modal>
        @endforeach
    @endif
@endsection
