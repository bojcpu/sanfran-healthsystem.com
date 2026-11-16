<?php

use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;

it('defaults the address form to San Francisco and offers all seven puroks', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    $this->actingAs($user)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertSee('San Francisco, Talibon, Bohol')
        ->assertSee('Purok 1')
        ->assertSee('Purok 7');
});

it('saves the selected purok together with the default locality', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    $this->actingAs($user)
        ->post(route('patients.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'birth_date' => '1990-01-01',
            'gender' => 'Female',
            'civil_status' => 'Single',
            'contact_number' => '09123456789',
            'purok' => 'Purok 3',
            'address' => 'San Francisco, Talibon, Bohol',
        ])
        ->assertRedirect(route('patients.index'));

    $this->assertDatabaseHas('patients', [
        'first_name' => 'Ana',
        'address' => 'Purok 3, San Francisco, Talibon, Bohol',
    ]);
});

it('requires a valid purok selection', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    $this->actingAs($user)
        ->post(route('patients.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'birth_date' => '1990-01-01',
            'gender' => 'Female',
            'civil_status' => 'Single',
            'contact_number' => '09123456789',
            'address' => 'San Francisco, Talibon, Bohol',
        ])
        ->assertSessionHasErrors('purok');
});

it('splits a saved purok from the address when editing a patient', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);
    $patient = Patient::factory()->createOne([
        'address' => 'Purok 5, San Francisco, Talibon, Bohol',
    ]);

    $this->actingAs($user)
        ->get(route('patients.edit', $patient))
        ->assertSuccessful()
        ->assertSee('value="Purok 5" selected', false)
        ->assertSee('value="San Francisco, Talibon, Bohol"', false);
});
