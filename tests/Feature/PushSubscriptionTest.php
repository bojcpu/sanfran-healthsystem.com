<?php

use App\Models\PushSubscription;
use App\Models\User;

it('stores a public push subscription and tracks its unread announcement count', function () {
    config()->set('services.webpush.public_key', str_repeat('A', 87));
    config()->set('services.webpush.private_key', str_repeat('a', 43));
    config()->set('services.webpush.subject', 'mailto:health@example.org');

    $endpoint = 'https://push.example.test/subscription/device-123';
    $subscription = [
        'endpoint' => $endpoint,
        'keys' => [
            'p256dh' => 'browser-public-key',
            'auth' => 'browser-auth-token',
        ],
    ];

    $this->postJson(route('push.subscribe'), array_replace($subscription, [
        'endpoint' => 'http://push.example.test/subscription/insecure',
    ]))->assertUnprocessable();

    $this->getJson(route('push.public-key'))
        ->assertOk()
        ->assertJsonPath('enabled', true)
        ->assertJsonPath('publicKey', str_repeat('A', 87));

    $this->postJson(route('push.subscribe'), $subscription)
        ->assertOk()
        ->assertJsonPath('subscribed', true);

    $storedSubscription = PushSubscription::query()->firstOrFail();
    expect($storedSubscription->user_id)->toBeNull();

    $storedSubscription->update(['unread_count' => 2]);

    $this->postJson(route('push.unread-count'), ['endpoint' => $endpoint])
        ->assertOk()
        ->assertJsonPath('unreadCount', 2);

    $this->postJson(route('push.mark-read'), ['endpoint' => $endpoint])
        ->assertOk()
        ->assertJsonPath('unreadCount', 0);

    expect($storedSubscription->fresh()->unread_count)->toBe(0);

    $this->postJson(route('push.unsubscribe'), ['endpoint' => $endpoint])
        ->assertOk()
        ->assertJsonPath('subscribed', false);

    $this->assertDatabaseMissing('push_subscriptions', [
        'endpoint_hash' => hash('sha256', $endpoint),
    ]);
});

it('does not enable push subscription until VAPID keys are configured', function () {
    config()->set('services.webpush.public_key', null);
    config()->set('services.webpush.private_key', null);

    $this->getJson(route('push.public-key'))
        ->assertStatus(503)
        ->assertJsonPath('enabled', false);
});

it('links a push subscription to the signed-in user while allowing anonymous subscriptions', function () {
    config()->set('services.webpush.public_key', str_repeat('A', 87));
    config()->set('services.webpush.private_key', str_repeat('a', 43));
    config()->set('services.webpush.subject', 'mailto:health@example.org');

    $user = User::factory()->create();
    $endpoint = 'https://push.example.test/subscription/authenticated-device';

    $this->actingAs($user)
        ->postJson(route('push.subscribe'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'browser-public-key',
                'auth' => 'browser-auth-token',
            ],
        ])
        ->assertOk();

    $subscription = PushSubscription::query()->where('endpoint_hash', hash('sha256', $endpoint))->firstOrFail();

    expect($subscription->user->is($user))->toBeTrue();
});

    it('does not enable push subscription with malformed VAPID keys', function () {
        config()->set('services.webpush.public_key', 'invalid-public-key');
        config()->set('services.webpush.private_key', 'invalid-private-key');
        config()->set('services.webpush.subject', 'mailto:health@example.org');

        $this->getJson(route('push.public-key'))
        ->assertStatus(503)
        ->assertJsonPath('enabled', false);
    });
