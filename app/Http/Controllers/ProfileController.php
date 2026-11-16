<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $updatedFields = [];

        if ($request->boolean('remove_profile_picture') && $user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->profile_picture = null;
            $updatedFields[] = 'profile picture removed';
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $user->profile_picture = $request->file('profile_picture')->store('profile-pictures', 'public');
            $updatedFields[] = 'profile picture';
        }

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
            $updatedFields[] = 'password';
        }

        $user->save();

        if ($updatedFields !== []) {
            $this->activityLog->log(
                'update',
                'Updated account settings: '.implode(', ', $updatedFields).'.',
                'Profile'
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Your account settings have been updated.');
    }
}
