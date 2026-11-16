<?php

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\User;

it('shows the public landing page before login', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertSeeInOrder([
        'PUBLIC ANNOUNCEMENT',
        'Health announcements for San Francisco.',
        'News about vaccines, care for mothers, and health events in Barangay San Francisco, Talibon, Bohol.',
    ]);
    $response->assertSeeInOrder([
        'SanFran HealthSys',
        'Barangay Health Center',
    ]);
    $response->assertSee('Login');
    $response->assertDontSee('Staff Login');
    $response->assertSee('Add to phone');
    $response->assertSee('manifest.webmanifest');
    $response->assertDontSee('Install as PWA');
    $response->assertDontSee('Install PWA');
    $response->assertDontSee('Sign In');
});

it('serves the PWA manifest', function () {
    $response = $this->get(route('pwa.manifest'));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/manifest+json');
    expect($response->json('display'))->toBe('standalone');
    expect($response->json('start_url'))->toBe('./');
});

it('shows the latest uploaded announcement in each module by default', function () {
    $user = User::factory()->create();

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Old Emergency Notice',
        'type' => AnnouncementType::Emergency,
        'created_at' => now()->subDays(2),
    ]);

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'New Emergency Notice',
        'type' => AnnouncementType::Emergency,
        'created_at' => now()->subDay(),
    ]);

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Latest General Notice',
        'type' => AnnouncementType::General,
        'created_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Recent')
        ->assertSeeInOrder([
            'New Emergency Notice',
            'Latest General Notice',
        ])
        ->assertDontSee('Old Emergency Notice');
});

it('counts only public announcements newer than the visitor read position', function () {
    $user = User::factory()->create();

    $lastRead = Announcement::factory()->for($user, 'postedBy')->create([
        'publish_date' => now()->toDateString(),
    ]);
    $newAnnouncement = Announcement::factory()->for($user, 'postedBy')->create([
        'publish_date' => now()->toDateString(),
    ]);
    Announcement::factory()->for($user, 'postedBy')->inactive()->create();
    Announcement::factory()->for($user, 'postedBy')->scheduled()->create();

    $this->getJson(route('public-announcements.unread-count', ['since_id' => $lastRead->id]))
        ->assertOk()
        ->assertJsonPath('unreadCount', 1)
        ->assertJsonPath('latestId', $newAnnouncement->id)
        ->assertJsonPath('latestUnreadUrl', route('home', ['announcement' => $newAnnouncement->id]));
});

it('opens a specifically requested announcement even when it is not the latest in its category', function () {
    $user = User::factory()->create();

    $requestedAnnouncement = Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Earlier Emergency Notice',
        'type' => AnnouncementType::Emergency,
        'created_at' => now()->subDays(2),
    ]);

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Latest Emergency Notice',
        'type' => AnnouncementType::Emergency,
        'created_at' => now()->subDay(),
    ]);

    $this->get(route('home', ['announcement' => $requestedAnnouncement->id]))
        ->assertSuccessful()
        ->assertSee('Earlier Emergency Notice')
        ->assertSee('data-open-announcement', false)
            ->assertSee('x-init="$nextTick(() => $el.click())"', false);
});

it('sorts landing announcements from oldest to newest when requested', function () {
    $user = User::factory()->create();

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Older Advisory',
        'type' => AnnouncementType::Advisory,
        'created_at' => now()->subDays(4),
    ]);

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Newer Advisory',
        'type' => AnnouncementType::Advisory,
        'created_at' => now()->subDay(),
    ]);

    $this->get(route('home', ['sort' => 'oldest']))
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Older Advisory',
            'Newer Advisory',
        ]);
});

it('filters landing announcements by type', function () {
    $user = User::factory()->create();

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Measles Vaccine Day',
        'type' => AnnouncementType::Immunization,
    ]);

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Wednesday Prenatal Hours',
        'type' => AnnouncementType::MaternalCare,
    ]);

    $this->get(route('home', ['type' => AnnouncementType::Immunization->value]))
        ->assertSuccessful()
        ->assertSee('Measles Vaccine Day')
        ->assertDontSee('Wednesday Prenatal Hours');
});

it('hides inactive expired and unpublished announcements from the landing page', function () {
    $user = User::factory()->create();

    Announcement::factory()->for($user, 'postedBy')->create([
        'title' => 'Visible Advisory',
        'type' => AnnouncementType::Advisory,
    ]);

    Announcement::factory()->for($user, 'postedBy')->inactive()->create([
        'title' => 'Hidden Inactive Notice',
    ]);

    Announcement::factory()->for($user, 'postedBy')->expired()->create([
        'title' => 'Hidden Expired Notice',
    ]);

    Announcement::factory()->for($user, 'postedBy')->scheduled()->create([
        'title' => 'Hidden Future Notice',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Visible Advisory')
        ->assertDontSee('Hidden Inactive Notice')
        ->assertDontSee('Hidden Expired Notice')
        ->assertDontSee('Hidden Future Notice');
});

it('lets staff publish a typed announcement that appears on the landing page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::SystemAdmin,
    ]);

    $this->actingAs($admin)
        ->post(route('announcements.store'), [
            'title' => 'Dengue Prevention Week',
            'content' => 'Clean up stagnant water around your homes this week.',
            'type' => AnnouncementType::Advisory->value,
            'status' => AnnouncementStatus::Active->value,
            'publish_date' => now()->toDateString(),
        ])
        ->assertRedirect(route('announcements.index'));

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Dengue Prevention Week')
        ->assertSee('Health Advisory');
});

it('shows the staff login page separately from the landing page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Staff Login')
        ->assertSee('Sign In')
        ->assertSee('Back to announcements');
});

it('keeps authenticated users on the public landing page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Dashboard')
        ->assertDontSee('Go to Staff Login');
});
