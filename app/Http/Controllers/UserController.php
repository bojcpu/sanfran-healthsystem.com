<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        if (! auth()->user()?->role->canViewUsers()) {
            abort(403);
        }

        $users = User::query()
            ->when(auth()->user()->isMidwife(), fn ($query) => $query->where('role', UserRole::Bhw))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => auth()->user()->assignableRoles(),
            'canManageUsers' => auth()->user()->canManageUsers(),
        ]);
    }

    public function create(): RedirectResponse
    {
        $this->authorizeUserManagement();

        return redirect()->route('users.index');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->validated());

        $this->activityLog->log('create', "Created user account: {$user->username}.", 'User Management');

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorizeUserManagement();
        $this->authorizeManagedUser($user);

        return view('users.edit', [
            'user' => $user,
            'roles' => $user->role === UserRole::SystemAdmin
                ? [UserRole::SystemAdmin, ...auth()->user()->assignableRoles()]
                : auth()->user()->assignableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeManagedUser($user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        $this->activityLog->log('update', "Updated user account: {$user->username}.", 'User Management');

        return redirect()->route('users.index')->with('success', 'User account updated successfully.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorizeUserManagement();
        $this->authorizeManagedUser($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if (! $user->is_active) {
            return back()->with('warning', 'This account is already inactive.');
        }

        $username = $user->username;
        $user->update(['is_active' => false]);

        $this->activityLog->log('update', "Deactivated user account: {$username}.", 'User Management');

        return redirect()->route('users.index')->with('success', 'User account set to inactive.');
    }

    private function authorizeUserManagement(): void
    {
        if (! auth()->user()?->canManageUsers()) {
            abort(403);
        }
    }

    private function authorizeManagedUser(User $user): void
    {
        if (! auth()->user()?->role->canManageUser($user->role)) {
            abort(403, 'You do not have permission to manage this user account.');
        }
    }
}
