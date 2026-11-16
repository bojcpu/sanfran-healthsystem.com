<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('displays the login page', function () {
    $response = $this->get(route('login'));

    $response->assertSuccessful();
    $response->assertSee('SanFran HealthSys');
    $response->assertSee('Staff Login');
});

it('allows staff to login with username and password', function () {
    $user = User::query()->where('username', 'admin')->first();

    $response = $this->post('/login', [
        'username' => 'admin',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('redirects authenticated users away from login', function () {
    $user = User::query()->where('username', 'admin')->first();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('dashboard'));
});

it('allows midwives to view bhw users without management controls', function () {
    $midwife = User::query()->where('username', 'midwife')->first();
    $bhw = User::query()->where('username', 'bhw')->first();

    $this->actingAs($midwife)
        ->get(route('users.index'))
        ->assertSuccessful()
        ->assertSee('bhw')
        ->assertDontSee('Add User')
        ->assertDontSee('Edit');

    $this->actingAs($midwife)
        ->get(route('users.create'))
        ->assertForbidden();

    $this->actingAs($midwife)
        ->get(route('users.edit', $bhw))
        ->assertForbidden();

    $this->actingAs($midwife)
        ->patch(route('users.deactivate', $bhw))
        ->assertForbidden();
});

it('restricts user management from bhw accounts', function () {
    $bhw = User::query()->where('username', 'bhw')->first();

    $this->actingAs($bhw)
        ->get(route('users.index'))
        ->assertForbidden();
});

it('allows system admins to access activity logs', function () {
    $admin = User::query()->where('username', 'admin')->first();

    $this->actingAs($admin)
        ->get(route('activity-logs.index'))
        ->assertSuccessful();
});

it('allows midwives to access activity logs', function () {
    $midwife = User::query()->where('username', 'midwife')->first();

    $this->actingAs($midwife)
        ->get(route('activity-logs.index'))
        ->assertSuccessful();
});

it('restricts activity logs from bhw accounts', function () {
    $bhw = User::query()->where('username', 'bhw')->first();

    $this->actingAs($bhw)
        ->get(route('activity-logs.index'))
        ->assertForbidden();
});

it('allows system admins to create midwife accounts', function () {
    $admin = User::query()->where('username', 'admin')->first();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'New Midwife',
            'username' => 'newmidwife',
            'email' => 'newmidwife@sanfran-healthsys.test',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'role' => UserRole::Midwife->value,
            'is_active' => true,
        ])
        ->assertRedirect(route('users.index'));

    expect(User::query()->where('username', 'newmidwife')->first()?->role)
        ->toBe(UserRole::Midwife);
});

it('prevents midwives from creating bhw accounts', function () {
    $midwife = User::query()->where('username', 'midwife')->first();

    $this->actingAs($midwife)
        ->post(route('users.store'), [
            'name' => 'New BHW',
            'username' => 'newbhw',
            'email' => 'newbhw@sanfran-healthsys.test',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'role' => UserRole::Bhw->value,
            'is_active' => true,
        ])
        ->assertForbidden();

    expect(User::query()->where('username', 'newbhw')->exists())->toBeFalse();
});

it('prevents midwives from creating midwife accounts', function () {
    $midwife = User::query()->where('username', 'midwife')->first();

    $this->actingAs($midwife)
        ->post(route('users.store'), [
            'name' => 'Another Midwife',
            'username' => 'anothermidwife',
            'email' => 'anothermidwife@sanfran-healthsys.test',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
            'role' => UserRole::Midwife->value,
            'is_active' => true,
        ])
        ->assertForbidden();
});
