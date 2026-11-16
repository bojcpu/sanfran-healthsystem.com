<?php

use App\Enums\UserRole;
use App\Models\CheckupRecord;
use App\Models\Patient;
use App\Models\User;

it('suggests matching patient records for the patient list search', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);
    $patient = Patient::factory()->createOne([
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'patient_code' => 'SF-SUGGEST-01',
        'contact_number' => '09123456789',
    ]);
    Patient::factory()->createOne(['first_name' => 'Unrelated']);

    $response = $this->actingAs($user)
        ->getJson(route('search.suggestions', ['source' => 'patients', 'q' => 'Maria']));

    $response->assertSuccessful()
        ->assertJsonFragment([
            'value' => 'Maria',
            'label' => $patient->full_name,
        ])
        ->assertJsonMissing(['value' => 'Unrelated']);
});

it('returns every matching patient name in the suggestions', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    foreach (range(1, 15) as $number) {
        Patient::factory()->createOne([
            'first_name' => "Match{$number}",
            'last_name' => 'Record',
        ]);
    }

    $this->actingAs($user)
        ->getJson(route('search.suggestions', ['source' => 'patients', 'q' => 'Match']))
        ->assertSuccessful()
        ->assertJsonCount(15, 'suggestions');
});

it('renders the suggestions endpoint as a same-origin path', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    $this->actingAs($user)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertSee('/search-suggestions/patients', false);
});

it('suggests patient names when another searchable field matches', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);
    $patient = Patient::factory()->createOne([
        'first_name' => 'Liza',
        'patient_code' => 'SF-CHECKUP-SUGGEST',
    ]);
    CheckupRecord::factory()->createOne([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'diagnosis' => 'Seasonal allergy',
    ]);

    $this->actingAs($user)
        ->getJson(route('search.suggestions', ['source' => 'checkups', 'q' => 'Seasonal']))
        ->assertSuccessful()
        ->assertJsonFragment([
            'value' => 'Seasonal',
            'label' => $patient->full_name,
        ]);

    $this->getJson(route('search.suggestions', ['source' => 'checkups', 'q' => 'Liza']))
        ->assertSuccessful()
        ->assertJsonFragment([
            'value' => 'Liza',
            'label' => $patient->full_name,
        ]);
});

it('does not expose user suggestions to users without permission to view users', function () {
    $user = User::factory()->createOne(['role' => UserRole::Bhw]);

    $this->actingAs($user)
        ->getJson(route('search.suggestions', ['source' => 'users', 'q' => 'staff']))
        ->assertForbidden();
});
