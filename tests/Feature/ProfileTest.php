<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('public');
});

it('allows authenticated users to update their password', function () {
    $user = User::query()->where('username', 'midwife')->first();

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'current_password' => 'password',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Hash::check('newpassword123', $user->fresh()->password))->toBeTrue();
});

it('requires the current password when changing password', function () {
    $user = User::query()->where('username', 'midwife')->first();

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'current_password' => 'wrong-password',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('current_password');
});

it('allows authenticated users to upload a profile picture', function () {
    $user = User::query()->where('username', 'midwife')->first();

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'profile_picture' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $user->refresh();

    expect($user->profile_picture)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_picture);
});

it('requires authentication to update profile', function () {
    $this->put(route('profile.update'), [
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ])->assertRedirect(route('login'));
});
