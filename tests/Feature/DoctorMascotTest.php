<?php

use App\Models\User;

it('does not show the clinic doctor on the public landing page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('data-doctor-mascot', false)
        ->assertDontSee('Ask a question')
        ->assertSee('h-[88px] w-[88px]', false);
});

it('shows the clinic doctor and system FAQs on authenticated pages', function () {
    /** @var User $user */
    $user = User::factory()->createOne();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('data-doctor-mascot', false)
        ->assertSee('How do I add a patient?')
        ->assertSee('Who can send a text, and how?')
        ->assertSee('What does the staff home screen show?');
});

it('does not show the clinic doctor on the login page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertDontSee('data-doctor-mascot', false)
        ->assertDontSee('Ask a question');
});
