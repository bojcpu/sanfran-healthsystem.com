<?php

use App\Enums\AnnouncementType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use App\Notifications\CheckupFollowUpScheduled;
use App\Notifications\ImmunizationNextScheduleScheduled;
use App\Notifications\MaternalCareFollowUpScheduled;
use App\Services\AnnouncementPushService;
use Illuminate\Support\Facades\Artisan;
use Mockery;

it('loads the checkup and maternal care create forms for authenticated staff', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Midwife,
    ]);

    Patient::create([
        'patient_code' => 'P-1001',
        'first_name' => 'Maria',
        'last_name' => 'Dela Cruz',
        'gender' => 'Female',
        'birth_date' => now()->subYears(28)->toDateString(),
        'civil_status' => 'Single',
        'contact_number' => '09123456789',
        'address' => 'Barangay San Francisco',
        'registered_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('checkups.create'))
        ->assertRedirect(route('checkups.index'));

    $this->actingAs($user)
        ->get(route('maternal-care.create'))
        ->assertSuccessful()
        ->assertSee('Medical History')
        ->assertSee('Physical Examination');
});

it('searches and filters checkup immunization and maternal care records', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Midwife,
    ]);

    $checkupPatient = Patient::factory()->createOne([
        'first_name' => 'CheckupSearchTarget',
        'patient_code' => 'CHK-SEARCH',
    ]);
    $otherCheckupPatient = Patient::factory()->createOne();
    $checkup = CheckupRecord::factory()->createOne([
        'patient_id' => $checkupPatient->id,
        'user_id' => $user->id,
        'diagnosis' => 'Searchable checkup diagnosis',
        'visit_date' => '2026-10-11',
    ]);
    CheckupRecord::factory()->createOne([
        'patient_id' => $otherCheckupPatient->id,
        'user_id' => $user->id,
        'diagnosis' => 'Unrelated checkup diagnosis',
        'visit_date' => '2026-10-12',
    ]);

    $immunizationPatient = Patient::factory()->createOne([
        'first_name' => 'ImmunizationSearchTarget',
        'patient_code' => 'IMM-SEARCH',
    ]);
    $otherImmunizationPatient = Patient::factory()->createOne();
    $immunization = ImmunizationRecord::factory()->createOne([
        'patient_id' => $immunizationPatient->id,
        'user_id' => $user->id,
        'vaccine_name' => 'Searchable vaccine',
        'vaccination_date' => '2026-10-11',
    ]);
    ImmunizationRecord::factory()->createOne([
        'patient_id' => $otherImmunizationPatient->id,
        'user_id' => $user->id,
        'vaccine_name' => 'Unrelated vaccine',
        'vaccination_date' => '2026-10-12',
    ]);

    $maternalPatient = Patient::factory()->createOne([
        'first_name' => 'MaternalSearchTarget',
        'gender' => 'Female',
        'patient_code' => 'MAT-SEARCH',
    ]);
    $otherMaternalPatient = Patient::factory()->createOne([
        'gender' => 'Female',
    ]);
    $maternalRecord = MaternalCareRecord::factory()->createOne([
        'patient_id' => $maternalPatient->id,
        'user_id' => $user->id,
        'visit_date' => '2026-10-11',
    ]);
    MaternalCareRecord::factory()->createOne([
        'patient_id' => $otherMaternalPatient->id,
        'user_id' => $user->id,
        'visit_date' => '2026-10-12',
    ]);

    $this->actingAs($user)
        ->get(route('checkups.index', ['search' => 'Searchable checkup diagnosis']))
        ->assertSuccessful()
        ->assertViewHas('checkups', fn ($records) => $records->total() === 1 && $records->first()->is($checkup));

    $this->actingAs($user)
        ->get(route('checkups.index', ['date' => '2026-10-11']))
        ->assertSuccessful()
        ->assertViewHas('checkups', fn ($records) => $records->total() === 1 && $records->first()->is($checkup));

    $this->actingAs($user)
        ->get(route('immunizations.index', ['search' => 'Searchable vaccine']))
        ->assertSuccessful()
        ->assertViewHas('immunizations', fn ($records) => $records->total() === 1 && $records->first()->is($immunization));

    $this->actingAs($user)
        ->get(route('immunizations.index', ['date' => '2026-10-11']))
        ->assertSuccessful()
        ->assertViewHas('immunizations', fn ($records) => $records->total() === 1 && $records->first()->is($immunization));

    $this->actingAs($user)
        ->get(route('maternal-care.index', ['search' => 'MaternalSearchTarget']))
        ->assertSuccessful()
        ->assertViewHas('records', fn ($records) => $records->total() === 1 && $records->first()->is($maternalRecord));

    $this->actingAs($user)
        ->get(route('maternal-care.index', ['date' => '2026-10-11']))
        ->assertSuccessful()
        ->assertViewHas('records', fn ($records) => $records->total() === 1 && $records->first()->is($maternalRecord));
});

it('allows system admins to view patient records but not create edit or archive them', function () {
    /** @var User $admin */
    $admin = User::factory()->systemAdmin()->createOne();
    $patient = Patient::factory()->create([
        'gender' => 'Female',
        'notes' => 'Patient profile details for admin viewing',
    ]);
    $checkup = CheckupRecord::factory()->create([
        'patient_id' => $patient->id,
        'diagnosis' => 'Admin view-only checkup',
    ]);
    $immunization = ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'vaccine_name' => 'Admin view-only vaccine',
    ]);
    $maternalCare = MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'findings' => 'Admin view-only maternal record',
    ]);

    $this->actingAs($admin)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertSee($patient->full_name)
        ->assertSee(route('patients.show', $patient), false)
        ->assertDontSee('Register Patient')
        ->assertDontSee(route('patients.edit', $patient), false)
        ->assertDontSee('Archive patient '.$patient->full_name);

    $this->actingAs($admin)
        ->get(route('patients.show', $patient))
        ->assertSuccessful()
        ->assertSee('Patient profile details for admin viewing')
        ->assertSee('Admin view-only checkup')
        ->assertSee('Admin view-only vaccine')
        ->assertSee('Admin view-only maternal record')
        ->assertDontSee('New Checkup')
        ->assertDontSee('New Immunization')
        ->assertDontSee(route('checkups.edit', $checkup), false)
        ->assertDontSee(route('immunizations.edit', $immunization), false)
        ->assertDontSee(route('maternal-care.edit', $maternalCare), false)
        ->assertDontSee('Edit');

    $this->actingAs($admin)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertSee('Admin view-only checkup')
        ->assertDontSee('New Checkup')
        ->assertDontSee(route('checkups.edit', $checkup), false);

    $this->actingAs($admin)
        ->get(route('immunizations.index'))
        ->assertSuccessful()
        ->assertSee('Admin view-only vaccine')
        ->assertDontSee('New Immunization')
        ->assertDontSee(route('immunizations.edit', $immunization), false);

    $this->actingAs($admin)
        ->get(route('maternal-care.index'))
        ->assertSuccessful()
        ->assertSee($patient->full_name)
        ->assertDontSee('New Record')
        ->assertDontSee(route('maternal-care.edit', $maternalCare), false);

    $this->actingAs($admin)->get(route('patients.create'))->assertForbidden();
    $this->actingAs($admin)->get(route('patients.edit', $patient))->assertForbidden();
    $this->actingAs($admin)->post(route('patients.store'), [])->assertForbidden();
    $this->actingAs($admin)->put(route('patients.update', $patient), [])->assertForbidden();
    $this->actingAs($admin)->patch(route('patients.update', $patient), [])->assertForbidden();
    $this->actingAs($admin)->delete(route('patients.destroy', $patient))->assertForbidden();

    $this->actingAs($admin)->get(route('checkups.create'))->assertForbidden();
    $this->actingAs($admin)->get(route('checkups.edit', $checkup))->assertForbidden();
    $this->actingAs($admin)->post(route('checkups.store'), [])->assertForbidden();
    $this->actingAs($admin)->put(route('checkups.update', $checkup), [])->assertForbidden();
    $this->actingAs($admin)->patch(route('checkups.update', $checkup), [])->assertForbidden();
    $this->actingAs($admin)->delete(route('checkups.destroy', $checkup))->assertForbidden();

    $this->actingAs($admin)->get(route('immunizations.create'))->assertForbidden();
    $this->actingAs($admin)->get(route('immunizations.edit', $immunization))->assertForbidden();
    $this->actingAs($admin)->post(route('immunizations.store'), [])->assertForbidden();
    $this->actingAs($admin)->put(route('immunizations.update', $immunization), [])->assertForbidden();
    $this->actingAs($admin)->patch(route('immunizations.update', $immunization), [])->assertForbidden();
    $this->actingAs($admin)->delete(route('immunizations.destroy', $immunization))->assertForbidden();

    $this->actingAs($admin)->get(route('maternal-care.create'))->assertForbidden();
    $this->actingAs($admin)->get(route('maternal-care.edit', $maternalCare))->assertForbidden();
    $this->actingAs($admin)->post(route('maternal-care.store'), [])->assertForbidden();
    $this->actingAs($admin)->put(route('maternal-care.update', $maternalCare), [])->assertForbidden();
    $this->actingAs($admin)->patch(route('maternal-care.update', $maternalCare), [])->assertForbidden();
    $this->actingAs($admin)->delete(route('maternal-care.destroy', $maternalCare))->assertForbidden();

    expect($patient->fresh()->notes)->toBe('Patient profile details for admin viewing')
        ->and($checkup->fresh()->diagnosis)->toBe('Admin view-only checkup')
        ->and($immunization->fresh()->vaccine_name)->toBe('Admin view-only vaccine')
        ->and($maternalCare->fresh()->findings)->toBe('Admin view-only maternal record');
});

it('only offers and accepts female patients for maternal care records', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Midwife,
    ]);
    $femalePatient = Patient::factory()->create([
        'gender' => 'Female',
    ]);
    $malePatient = Patient::factory()->create([
        'gender' => 'Male',
    ]);

    $this->actingAs($user)
        ->get(route('maternal-care.create'))
        ->assertSuccessful()
        ->assertSee($femalePatient->patient_code)
        ->assertDontSee($malePatient->patient_code)
        ->assertSee('Medical History');

    $this->actingAs($user)
        ->post(route('maternal-care.store'), [
            'patient_id' => $malePatient->id,
            'pregnancy_number' => 1,
            'visit_date' => '2026-10-03',
        ])
        ->assertSessionHasErrors('patient_id');
});

it('shows prenatal details in the patient maternal care tab', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);
    $patient = Patient::factory()->create([
        'gender' => 'Female',
    ]);
    MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'pregnancy_number' => 1,
        'para' => 0,
        'trimester' => '2nd Trimester',
        'gestational_weeks' => 24,
        'gestational_days' => 3,
        'visit_date' => '2026-10-16',
        'expected_delivery_date' => '2027-01-30',
        'follow_up_date' => '2026-10-30',
    ]);

    $this->actingAs($user)
        ->get(route('patients.show', $patient))
        ->assertSuccessful()
        ->assertSee('Gravida')
        ->assertSee('Para')
        ->assertSee('Current Trimester')
        ->assertSee('2nd Trimester')
        ->assertSee('AOG')
        ->assertSee('24 weeks 3 days')
        ->assertSee('Expected Delivery Date')
        ->assertSee('Jan 30, 2027')
        ->assertSee('Visit Date')
        ->assertSee('Oct 16, 2026')
        ->assertSee('Prenatal Follow-up Date')
        ->assertSee('Oct 30, 2026');
});

it('shows immunization details in the patient immunizations tab', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);
    $patient = Patient::factory()->create();
    ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'vaccine_name' => 'Hepatitis B',
        'dose' => '1st Dose',
        'vaccination_date' => '2026-10-16',
        'next_schedule' => '2026-11-16',
        'remarks' => 'No adverse reaction.',
    ]);

    $this->actingAs($user)
        ->get(route('patients.show', $patient))
        ->assertSuccessful()
        ->assertSee('Vaccine Name')
        ->assertSee('Hepatitis B')
        ->assertSee('Dose')
        ->assertSee('1st Dose')
        ->assertSee('Vaccination Date')
        ->assertSee('Oct 16, 2026')
        ->assertSee('Next Schedule')
        ->assertSee('Nov 16, 2026')
        ->assertSee('Remarks')
        ->assertSee('No adverse reaction.');
});

it('requires patient contact numbers to contain exactly 11 digits', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);

    $this->actingAs($user)
        ->post(route('patients.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'birth_date' => '1990-01-01',
            'gender' => 'Female',
            'civil_status' => 'Single',
            'contact_number' => '0912345678',
            'email' => null,
            'purok' => 'Purok 1',
            'address' => 'Barangay San Francisco',
            'emergency_contact_number' => '091234567890',
        ])
        ->assertSessionHasErrors(['contact_number', 'emergency_contact_number']);

    $this->actingAs($user)
        ->post(route('patients.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'birth_date' => '1990-01-01',
            'gender' => 'Female',
            'civil_status' => 'Single',
            'purok' => 'Purok 1',
            'address' => 'Barangay San Francisco',
        ])
        ->assertSessionHasErrors(['contact_number']);
});

it('saves antenatal details and calculates the expected delivery date from LMP', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Midwife,
    ]);
    $patient = Patient::factory()->create([
        'gender' => 'Female',
    ]);

    $this->actingAs($user)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'para' => 0,
            'visit_date' => '2026-10-03',
            'last_menstrual_period' => '2026-07-11',
            'spouse_name' => 'Mika Santos',
            'living_children' => 0,
            'birth_plan' => 'Facility Delivery',
            'hospital' => 'Talibon District Hospital',
            'rhu' => 'Talibon RHU',
            'birth_attendant' => 'BHW',
            'blood_pressure' => '120/80',
            'pulse_rate' => '82',
            'respiratory_rate' => '20',
            'temperature' => '36.7',
            'weight' => '55',
            'height' => '156',
            'present_history' => ['severe_headache'],
            'family_history' => ['hypertension'],
            'conjunctiva_findings' => ['pale'],
            'vaginal_findings' => ['discharge'],
            'findings' => 'Normal pregnancy, 12 weeks AOG',
            'recommendations' => 'Continue prenatal vitamins',
        ])
        ->assertRedirect(route('maternal-care.index'));

    $record = MaternalCareRecord::query()->firstOrFail();

    expect($record->expected_delivery_date->toDateString())->toBe('2027-04-17')
        ->and($record->last_menstrual_period->toDateString())->toBe('2026-07-11')
        ->and($record->spouse_name)->toBe('Mika Santos')
        ->and($record->present_history)->toBe(['severe_headache'])
        ->and($record->vaginal_findings)->toBe(['discharge']);

    $this->get(route('maternal-care.edit', $record))
        ->assertSuccessful()
        ->assertSee('Medical History')
        ->assertSee('Mika Santos')
        ->assertSee('Talibon District Hospital')
        ->assertSee('name="pregnancy_number"', false)
        ->assertSee('name="para"', false)
        ->assertSee('name="trimester"', false)
        ->assertSee('name="gestational_weeks"', false)
        ->assertSee('name="gestational_days"', false)
        ->assertSee('name="expected_delivery_date"', false);

    $this->put(route('maternal-care.update', $record), [
        'patient_id' => $patient->id,
        'pregnancy_number' => 3,
        'para' => 2,
        'trimester' => '2nd Trimester',
        'gestational_weeks' => 24,
        'gestational_days' => 3,
        'visit_date' => '2026-10-03',
        'last_menstrual_period' => '2026-07-11',
        'expected_delivery_date' => '2027-04-20',
    ])->assertRedirect(route('maternal-care.index'));

    $record->refresh();

    expect($record->pregnancy_number)->toBe(3)
        ->and($record->para)->toBe(2)
        ->and($record->trimester)->toBe('2nd Trimester')
        ->and($record->gestational_weeks)->toBe(24)
        ->and($record->gestational_days)->toBe(3)
        ->and($record->expected_delivery_date->toDateString())->toBe('2027-04-20');
});

it('only notifies midwives when the prenatal follow-up is within the 3-day reminder window', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $patient = Patient::factory()->create(['gender' => 'Female']);
    $farFutureFollowUpDate = today()->addDays(10)->toDateString();
    $threeDayFollowUpDate = today()->addDays(3)->toDateString();

    $this->actingAs($midwife)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => today()->subDay()->toDateString(),
            'follow_up_date' => $farFutureFollowUpDate,
        ])
        ->assertRedirect(route('maternal-care.index'));

    expect($midwife->notifications()->count())->toBe(0);

    $this->actingAs($midwife)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => today()->subDay()->toDateString(),
            'follow_up_date' => $threeDayFollowUpDate,
        ])
        ->assertRedirect(route('maternal-care.index'));

    $notification = $midwife->notifications()->firstOrFail();

    expect($notification->type)->toBe(MaternalCareFollowUpScheduled::class)
        ->and($notification->data['follow_up_date'])->toBe($threeDayFollowUpDate)
        ->and($notification->data['message'])->toContain('3 days');
});

it('notifies active midwives when a prenatal follow-up date is set or changed', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne(['role' => UserRole::Bhw]);
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $inactiveMidwife = User::factory()->midwife()->createOne(['is_active' => false]);
    $patient = Patient::factory()->create(['gender' => 'Female']);
    $threeDayFollowUpDate = today()->addDays(3)->toDateString();

    $this->actingAs($midwife)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => '2026-10-03',
            'follow_up_date' => $threeDayFollowUpDate,
        ])
        ->assertRedirect(route('maternal-care.index'));

    $record = MaternalCareRecord::query()->firstOrFail();
    $notification = $midwife->notifications()->firstOrFail();

    expect($record->follow_up_date->toDateString())->toBe($threeDayFollowUpDate)
        ->and($notification->type)->toBe(MaternalCareFollowUpScheduled::class)
        ->and($notification->data['follow_up_date'])->toBe($threeDayFollowUpDate)
        ->and($notification->user_id)->toBe($midwife->id)
        ->and($notification->notifiable->is($midwife))->toBeTrue()
        ->and($notification->maternal_care_record_id)->toBe($record->id)
        ->and($notification->source)->toBeInstanceOf(MaternalCareRecord::class)
        ->and($notification->source->is($record))->toBeTrue()
        ->and($bhw->notifications()->count())->toBe(0)
        ->and($inactiveMidwife->notifications()->count())->toBe(0);

    $this->actingAs($midwife)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee($patient->full_name)
        ->assertSee(Carbon\Carbon::parse($threeDayFollowUpDate)->format('M d, Y'));

    $this->patch(route('notifications.read', $notification->id))
        ->assertRedirect(route('notifications.index'));

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();

    $this->actingAs($midwife)
        ->put(route('maternal-care.update', $record), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => '2026-10-03',
            'follow_up_date' => today()->addDays(2)->toDateString(),
        ])
        ->assertRedirect(route('maternal-care.index'));

    $replacementNotification = $midwife->unreadNotifications()->firstOrFail();

    expect($midwife->notifications()->whereNull('read_at')->count())->toBe(1)
        ->and($replacementNotification->data['follow_up_date'])->toBe(today()->addDays(2)->toDateString());
});

it('opens and highlights the maternal care record from its notification and marks it as read', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $patient = Patient::factory()->create(['gender' => 'Female']);
    $otherRecord = MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'visit_date' => today()->subDays(10)->toDateString(),
    ]);

    $this->actingAs($midwife)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => today()->toDateString(),
            'follow_up_date' => today()->addDays(3)->toDateString(),
        ])
        ->assertRedirect(route('maternal-care.index'));

    $createdRecord = MaternalCareRecord::query()->latest('id')->firstOrFail();
    $notification = $midwife->notifications()->firstOrFail();
    $targetUrl = route('maternal-care.index', ['highlight' => $createdRecord->id])
        .'#maternal-care-record-'.$createdRecord->id;

    $this->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee(route('notifications.open', $notification->id), false);

    $this->get(route('notifications.open', $notification->id))
        ->assertRedirect($targetUrl);

    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->get(route('maternal-care.index', ['highlight' => $createdRecord->id]))
        ->assertSuccessful()
        ->assertSee('id="maternal-care-record-'.$createdRecord->id.'"', false)
        ->assertSee('id="maternal-care-record-'.$otherRecord->id.'"', false)
        ->assertSee('notification-highlight', false);
});

it('opens and highlights checkup immunization and announcement items from notifications', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $patient = Patient::factory()->create();
    $checkup = CheckupRecord::factory()->create(['patient_id' => $patient->id]);
    $otherCheckup = CheckupRecord::factory()->create(['patient_id' => $patient->id]);
    $immunization = ImmunizationRecord::factory()->create(['patient_id' => $patient->id]);
    $otherImmunization = ImmunizationRecord::factory()->create(['patient_id' => $patient->id]);
    $announcement = Announcement::factory()->create(['posted_by' => $midwife->id]);
    $otherAnnouncement = Announcement::factory()->create(['posted_by' => $midwife->id]);

    $midwife->notify(new CheckupFollowUpScheduled($checkup));
    $midwife->notify(new ImmunizationNextScheduleScheduled($immunization));
    $midwife->notify(new AnnouncementPublished($announcement));

    $notifications = $midwife->notifications()->get()->keyBy('type');
    $targets = [
        CheckupFollowUpScheduled::class => [
            $notifications[CheckupFollowUpScheduled::class],
            route('checkups.index', ['highlight' => $checkup->id]).'#checkup-record-'.$checkup->id,
            route('checkups.index', ['highlight' => $checkup->id]),
            'checkup-record-'.$checkup->id,
            'checkup-record-'.$otherCheckup->id,
        ],
        ImmunizationNextScheduleScheduled::class => [
            $notifications[ImmunizationNextScheduleScheduled::class],
            route('immunizations.index', ['highlight' => $immunization->id]).'#immunization-record-'.$immunization->id,
            route('immunizations.index', ['highlight' => $immunization->id]),
            'immunization-record-'.$immunization->id,
            'immunization-record-'.$otherImmunization->id,
        ],
        AnnouncementPublished::class => [
            $notifications[AnnouncementPublished::class],
            route('announcements.index', ['highlight' => $announcement->id]).'#announcement-'.$announcement->id,
            route('announcements.index', ['highlight' => $announcement->id]),
            'announcement-'.$announcement->id,
            'announcement-'.$otherAnnouncement->id,
        ],
    ];

    foreach ($targets as [$notification, $redirect, $listUrl, $recordId, $otherRecordId]) {
        $this->get(route('notifications.open', $notification->id))
            ->assertRedirect($redirect);

        expect($notification->fresh()->read_at)->not->toBeNull();

        $this->get($listUrl)
            ->assertSuccessful()
            ->assertSee('id="'.$recordId.'"', false)
            ->assertSee('id="'.$otherRecordId.'"', false)
            ->assertSee('notification-highlight', false);
    }
});

it('notifies active midwives about checkup follow-ups and immunization schedules', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne(['role' => UserRole::Bhw]);
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $inactiveMidwife = User::factory()->midwife()->createOne(['is_active' => false]);
    $patient = Patient::factory()->create();
    $followUpDate = today()->addDays(3)->toDateString();
    $nextSchedule = today()->addDays(2)->toDateString();

    $this->actingAs($bhw)
        ->post(route('checkups.store'), [
            'patient_id' => $patient->id,
            'visit_date' => today()->toDateString(),
            'follow_up_date' => $followUpDate,
        ])
        ->assertRedirect(route('checkups.index'));

    $this->post(route('immunizations.store'), [
        'patient_id' => $patient->id,
        'vaccine_name' => 'Hepatitis B',
        'dose' => 'Dose 2',
        'vaccination_date' => today()->toDateString(),
        'next_schedule' => $nextSchedule,
    ])->assertRedirect(route('immunizations.index'));

    $checkupNotification = $midwife->notifications()->where('type', CheckupFollowUpScheduled::class)->firstOrFail();
    $immunizationNotification = $midwife->notifications()->where('type', ImmunizationNextScheduleScheduled::class)->firstOrFail();

    expect($checkupNotification->data['follow_up_date'])->toBe($followUpDate)
        ->and($checkupNotification->data['message'])->toContain('3 days')
        ->and($checkupNotification->data['url'])->toBe(route('checkups.index'))
        ->and($checkupNotification->checkup_record_id)->toBe($checkupNotification->data['record_id'])
        ->and($checkupNotification->source)->toBeInstanceOf(CheckupRecord::class)
        ->and($immunizationNotification->data['next_schedule'])->toBe($nextSchedule)
        ->and($immunizationNotification->data['message'])->toContain('Hepatitis B')
        ->and($immunizationNotification->data['url'])->toBe(route('immunizations.index'))
        ->and($immunizationNotification->immunization_record_id)->toBe($immunizationNotification->data['record_id'])
        ->and($immunizationNotification->source)->toBeInstanceOf(ImmunizationRecord::class)
        ->and($bhw->notifications()->count())->toBe(0)
        ->and($inactiveMidwife->notifications()->count())->toBe(0);

    $this->actingAs($midwife)
        ->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('View Checkups')
        ->assertSee('View Immunizations');
});

it('notifies active midwives when a public announcement is published', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $anotherMidwife = User::factory()->midwife()->createOne();
    $inactiveMidwife = User::factory()->midwife()->createOne(['is_active' => false]);
    $pushService = Mockery::mock(AnnouncementPushService::class);
    $pushService->shouldReceive('send')->once();
    $this->app->instance(AnnouncementPushService::class, $pushService);

    $this->actingAs($midwife)
        ->post(route('announcements.store'), [
            'title' => 'Community health day',
            'content' => 'Visit the health center this week.',
            'type' => AnnouncementType::General->value,
            'status' => 'active',
            'publish_date' => today()->toDateString(),
        ])
        ->assertRedirect(route('announcements.index'));

    $notification = $midwife->unreadNotifications()->firstOrFail();

    expect($notification->type)->toBe(AnnouncementPublished::class)
        ->and($notification->data['message'])->toBe('New public announcement: Community health day')
        ->and($notification->announcement_id)->toBe($notification->data['announcement_id'])
        ->and($notification->source)->toBeInstanceOf(Announcement::class)
        ->and($notification->source->title)->toBe('Community health day')
        ->and($anotherMidwife->unreadNotifications()->count())->toBe(1)
        ->and($inactiveMidwife->notifications()->count())->toBe(0);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('aria-label="Notifications"', false)
        ->assertSee('>1</span>', false);

    $this->get(route('notifications.unread-count'))
        ->assertSuccessful()
        ->assertExactJson(['unreadCount' => 1]);

    $this->get(route('notifications.index'))
        ->assertSuccessful()
        ->assertSee('New public announcement: Community health day')
        ->assertSee('View announcement');
});

it('sends schedule reminders within three days to active midwives once per schedule', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $inactiveMidwife = User::factory()->midwife()->createOne(['is_active' => false]);
    $patient = Patient::factory()->create();
    $scheduledDate = today()->addDays(3)->toDateString();

    $dueRecord = ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'next_schedule' => $scheduledDate,
    ]);
    ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'next_schedule' => today()->addDays(2)->toDateString(),
    ]);
    ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'next_schedule' => today()->addDays(4)->toDateString(),
    ]);

    Artisan::call('records:send-schedule-reminders');

    $immunizationNotification = $midwife->notifications()
        ->where('type', ImmunizationNextScheduleScheduled::class)
        ->firstOrFail();

    expect($immunizationNotification->data['record_id'])->toBe($dueRecord->id)
        ->and($immunizationNotification->data['next_schedule'])->toBe($scheduledDate)
        ->and($midwife->notifications()->count())->toBe(2)
        ->and($inactiveMidwife->notifications()->count())->toBe(0);

    Artisan::call('records:send-schedule-reminders');

    expect($midwife->notifications()->count())->toBe(2);
});

it('notifies midwives when existing records enter the three-day reminder window', function () {
    $midwife = User::factory()->midwife()->create();
    $inactiveMidwife = User::factory()->midwife()->create(['is_active' => false]);
    $patient = Patient::factory()->create(['gender' => 'Female']);
    $scheduledDate = today()->addDays(4)->toDateString();

    CheckupRecord::factory()->create([
        'patient_id' => $patient->id,
        'follow_up_date' => $scheduledDate,
    ]);
    ImmunizationRecord::factory()->create([
        'patient_id' => $patient->id,
        'next_schedule' => $scheduledDate,
    ]);
    MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'follow_up_date' => $scheduledDate,
    ]);

    Artisan::call('records:send-schedule-reminders');
    expect($midwife->notifications()->count())->toBe(0);

    $this->travelTo(now()->addDay());
    Artisan::call('records:send-schedule-reminders');

    expect($midwife->notifications()->count())->toBe(3)
        ->and($midwife->notifications()->where('type', CheckupFollowUpScheduled::class)->value('data->scheduled_date'))->toBe($scheduledDate)
        ->and($midwife->notifications()->where('type', ImmunizationNextScheduleScheduled::class)->value('data->scheduled_date'))->toBe($scheduledDate)
        ->and($midwife->notifications()->where('type', MaternalCareFollowUpScheduled::class)->value('data->scheduled_date'))->toBe($scheduledDate)
        ->and($inactiveMidwife->notifications()->count())->toBe(0);

    Artisan::call('records:send-schedule-reminders');
    expect($midwife->notifications()->count())->toBe(3);
});

it('checks for three-day follow-up reminders when a midwife opens the notification bell', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->create();
    $patient = Patient::factory()->create(['gender' => 'Female']);
    $scheduledDate = today()->addDays(4)->toDateString();

    MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'follow_up_date' => $scheduledDate,
    ]);

    expect($midwife->notifications()->count())->toBe(0);

    $this->travelTo(now()->addDay());

    $this->actingAs($midwife)
        ->get(route('notifications.unread-count'))
        ->assertSuccessful()
        ->assertExactJson(['unreadCount' => 1]);

    expect($midwife->unreadNotifications()->where('type', MaternalCareFollowUpScheduled::class)->firstOrFail()->data['scheduled_date'])
        ->toBe($scheduledDate);
});

it('replaces checkup and immunization reminders when their schedule dates change', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne(['role' => UserRole::Bhw]);
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    $patient = Patient::factory()->create();

    $this->actingAs($bhw)
        ->post(route('checkups.store'), [
            'patient_id' => $patient->id,
            'visit_date' => today()->toDateString(),
            'follow_up_date' => today()->addDays(3)->toDateString(),
        ])
        ->assertRedirect(route('checkups.index'));

    $this->post(route('immunizations.store'), [
        'patient_id' => $patient->id,
        'vaccine_name' => 'Influenza',
        'vaccination_date' => today()->toDateString(),
        'next_schedule' => today()->addDays(3)->toDateString(),
    ])->assertRedirect(route('immunizations.index'));

    $checkup = CheckupRecord::query()->firstOrFail();
    $immunization = ImmunizationRecord::query()->firstOrFail();

    $this->put(route('checkups.update', $checkup), [
        'patient_id' => $patient->id,
        'visit_date' => today()->toDateString(),
        'follow_up_date' => today()->addDays(2)->toDateString(),
    ])->assertRedirect(route('checkups.index'));

    $this->put(route('immunizations.update', $immunization), [
        'patient_id' => $patient->id,
        'vaccine_name' => 'Influenza',
        'vaccination_date' => today()->toDateString(),
        'next_schedule' => today()->addDays(2)->toDateString(),
    ])->assertRedirect(route('immunizations.index'));

    expect($midwife->notifications()->count())->toBe(4)
        ->and($midwife->unreadNotifications()->count())->toBe(2)
        ->and($midwife->unreadNotifications()->where('type', CheckupFollowUpScheduled::class)->firstOrFail()->data['follow_up_date'])->toBe(today()->addDays(2)->toDateString())
        ->and($midwife->unreadNotifications()->where('type', ImmunizationNextScheduleScheduled::class)->firstOrFail()->data['next_schedule'])->toBe(today()->addDays(2)->toDateString());
});

it('shows maternal care records to BHW without write actions', function () {
    /** @var User $midwife */
    $midwife = User::factory()->midwife()->createOne();
    /** @var User $bhw */
    $bhw = User::factory()->createOne(['role' => UserRole::Bhw]);
    $patient = Patient::factory()->create([
        'gender' => 'Female',
    ]);
    $record = MaternalCareRecord::factory()->create([
        'patient_id' => $patient->id,
        'user_id' => $midwife->id,
        'pregnancy_number' => 2,
        'trimester' => '2nd Trimester',
        'visit_date' => '2026-10-03',
    ]);

    $this->actingAs($bhw)
        ->get(route('maternal-care.index'))
        ->assertSuccessful()
        ->assertSee('Trimester Period')
        ->assertDontSee('Pregnancy #')
        ->assertSee('2nd Trimester')
        ->assertSee('Oct 03, 2026')
        ->assertDontSee('New Record')
        ->assertDontSee(route('maternal-care.edit', $record), false)
        ->assertDontSee('Archive this maternal care record?');

    $this->actingAs($bhw)
        ->get(route('maternal-care.create'))
        ->assertForbidden();

    $this->actingAs($bhw)
        ->get(route('maternal-care.edit', $record))
        ->assertForbidden();

    $this->actingAs($bhw)
        ->post(route('maternal-care.store'), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 1,
            'visit_date' => '2026-10-03',
        ])
        ->assertForbidden();

    $this->actingAs($bhw)
        ->put(route('maternal-care.update', $record), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 3,
            'visit_date' => '2026-10-03',
        ])
        ->assertForbidden();

    $this->actingAs($bhw)
        ->patch(route('maternal-care.update', $record), [
            'patient_id' => $patient->id,
            'pregnancy_number' => 3,
            'visit_date' => '2026-10-03',
        ])
        ->assertForbidden();

    $this->actingAs($bhw)
        ->delete(route('maternal-care.destroy', $record))
        ->assertForbidden();

    expect(MaternalCareRecord::query()->count())->toBe(1)
        ->and($record->fresh()->pregnancy_number)->toBe(2);
});

it('loads the checkup, immunization, and maternal care modules for a BHW', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);

    $this->actingAs($user)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertSee('Checkups');

    $this->actingAs($user)
        ->get(route('immunizations.index'))
        ->assertSuccessful()
        ->assertSee('Immunizations');

    $this->actingAs($user)
        ->get(route('maternal-care.index'))
        ->assertSuccessful()
        ->assertSee('Maternal Care');
});

it('shows each patient record with the fields from its designated module', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);

    $patient = Patient::create([
        'patient_code' => 'P-3001',
        'first_name' => 'Ana',
        'last_name' => 'Reyes',
        'gender' => 'Female',
        'birth_date' => now()->subYears(30)->toDateString(),
        'civil_status' => 'Married',
        'contact_number' => '09123456789',
        'address' => 'Barangay San Francisco',
        'registered_by' => $user->id,
    ]);

    CheckupRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'visit_date' => '2026-09-01',
        'complaint' => 'Persistent headache',
        'diagnosis' => 'Migraine',
        'treatment' => 'Rest and hydration',
        'prescription' => 'Paracetamol',
        'attending_personnel' => 'Nurse Santos',
        'notes' => 'Return if symptoms worsen',
        'follow_up_date' => '2026-09-08',
    ]);
    ImmunizationRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'vaccine_name' => 'Hepatitis B',
        'dose' => 'Dose 2',
        'vaccination_date' => '2026-09-02',
        'next_schedule' => '2026-10-02',
        'remarks' => 'No adverse reaction',
    ]);
    $maternalRecord = MaternalCareRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'pregnancy_number' => 2,
        'para' => 1,
        'trimester' => '2nd Trimester',
        'gestational_weeks' => 24,
        'gestational_days' => 3,
        'expected_delivery_date' => '2027-01-15',
        'visit_date' => '2026-09-03',
        'weight' => 62.5,
        'blood_pressure' => '110/70',
        'findings' => 'Fetal heartbeat normal',
        'recommendations' => 'Continue prenatal vitamins',
        'remarks' => 'Follow-up in four weeks',
    ]);

    $this->actingAs($user)
        ->get(route('patients.show', $patient))
        ->assertSuccessful()
        ->assertSee('Persistent headache')
        ->assertSee('Migraine')
        ->assertSee('Paracetamol')
        ->assertSee('Hepatitis B')
        ->assertSee('Dose 2')
        ->assertSee('No adverse reaction')
        ->assertSee('2nd Trimester')
        ->assertSee('24 weeks 3 days')
        ->assertSee('110/70')
        ->assertSee('Fetal heartbeat normal')
        ->assertSee('Continue prenatal vitamins')
        ->assertSee('Follow-up in four weeks')
        ->assertDontSee(route('maternal-care.create', ['patient_id' => $patient->id]), false)
        ->assertDontSee(route('maternal-care.edit', $maternalRecord), false)
        ->assertDontSee('SMS History')
        ->assertDontSee('No SMS history.');
});

it('keeps all record modules available when a linked patient is archived', function () {
    /** @var User $user */
    $user = User::factory()->createOne([
        'role' => UserRole::Bhw,
    ]);

    $patient = Patient::create([
        'patient_code' => 'P-2001',
        'first_name' => 'Maria',
        'last_name' => 'Dela Cruz',
        'gender' => 'Female',
        'birth_date' => now()->subYears(28)->toDateString(),
        'civil_status' => 'Single',
        'contact_number' => '09123456789',
        'address' => 'Barangay San Francisco',
        'registered_by' => $user->id,
    ]);

    CheckupRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'visit_date' => now()->toDateString(),
    ]);
    ImmunizationRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'vaccine_name' => 'BCG',
        'vaccination_date' => now()->toDateString(),
    ]);
    MaternalCareRecord::create([
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'visit_date' => now()->toDateString(),
    ]);

    $patient->delete();

    $this->actingAs($user)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertSee('Patient record unavailable');

    $this->actingAs($user)
        ->get(route('immunizations.index'))
        ->assertSuccessful()
        ->assertSee('Patient record unavailable');

    $this->actingAs($user)
        ->get(route('maternal-care.index'))
        ->assertSuccessful()
        ->assertSee('Patient record unavailable');
});
