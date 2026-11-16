<?php

use App\Enums\ArchiveRecordType;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;

it('shows the archive tab to a barangay health worker', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();

    $this->actingAs($bhw)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Archive');
});

it('lets a barangay health worker archive a patient so it leaves the table', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();
    $patient = Patient::factory()->create([
        'first_name' => 'Archive',
        'middle_name' => null,
        'last_name' => 'Patient',
    ]);

    $this->actingAs($bhw)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertSee($patient->full_name)
        ->assertSee('Archive patient '.$patient->full_name);

    $this->actingAs($bhw)
        ->delete(route('patients.destroy', $patient))
        ->assertRedirect(route('patients.index'));

    $this->assertSoftDeleted($patient);

    $this->actingAs($bhw)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertDontSee($patient->patient_code);
});

it('does not let a barangay health worker archive or restore a maternal care record', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();
    $record = MaternalCareRecord::factory()->create();

    $this->actingAs($bhw)
        ->delete(route('maternal-care.destroy', $record))
        ->assertForbidden();

    $record->delete();

    $this->actingAs($bhw)
        ->get(route('archive.index', ['type' => ArchiveRecordType::MaternalCare->value]))
        ->assertSuccessful()
        ->assertSee($record->patient->full_name)
        ->assertDontSee('Restore this record to the active list?');

    $this->actingAs($bhw)
        ->post(route('archive.restore', [
            'type' => ArchiveRecordType::MaternalCare->value,
            'id' => $record->id,
        ]))
        ->assertForbidden();

    $this->assertSoftDeleted($record);
});

it('lists an archived patient in the archive tab and restores it', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();
    $patient = Patient::factory()->create([
        'first_name' => 'Restore',
        'middle_name' => null,
        'last_name' => 'Patient',
    ]);
    $patient->delete();

    $this->actingAs($bhw)
        ->get(route('archive.index', ['type' => ArchiveRecordType::Patients->value]))
        ->assertSuccessful()
        ->assertSee($patient->patient_code)
        ->assertSee('Restore');

    $this->actingAs($bhw)
        ->post(route('archive.restore', [
            'type' => ArchiveRecordType::Patients->value,
            'id' => $patient->id,
        ]))
        ->assertRedirect(route('archive.index', ['type' => ArchiveRecordType::Patients->value]));

    $this->assertNotSoftDeleted($patient);

    $this->actingAs($bhw)
        ->get(route('patients.index'))
        ->assertSuccessful()
        ->assertSee($patient->patient_code);
});

it('lets a barangay health worker archive and restore a checkup record', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();
    $checkup = CheckupRecord::factory()->create([
        'diagnosis' => 'Archive checkup diagnosis',
    ]);

    $this->actingAs($bhw)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertSee('Archive checkup diagnosis')
        ->assertSee('Archive this checkup record?');

    $this->actingAs($bhw)
        ->delete(route('checkups.destroy', $checkup))
        ->assertRedirect(route('checkups.index'));

    $this->assertSoftDeleted($checkup);

    $this->actingAs($bhw)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertDontSee('Archive checkup diagnosis');

    $this->actingAs($bhw)
        ->get(route('archive.index', ['type' => ArchiveRecordType::Checkups->value]))
        ->assertSuccessful()
        ->assertSee('Archive checkup diagnosis');

    $this->actingAs($bhw)
        ->post(route('archive.restore', [
            'type' => ArchiveRecordType::Checkups->value,
            'id' => $checkup->id,
        ]))
        ->assertRedirect(route('archive.index', ['type' => ArchiveRecordType::Checkups->value]));

    $this->assertNotSoftDeleted($checkup);
});

it('redirects guests away from the archive tab', function () {
    $this->get(route('archive.index'))
        ->assertRedirect(route('login'));
});

it('does not let a barangay health worker restore an announcement', function () {
    /** @var User $bhw */
    $bhw = User::factory()->createOne();
    $announcement = Announcement::factory()->create([
        'title' => 'Hidden advisory',
    ]);
    $announcement->delete();

    $this->actingAs($bhw)
        ->get(route('archive.index', ['type' => ArchiveRecordType::Announcements->value]))
        ->assertSuccessful()
        ->assertDontSee('Hidden advisory');

    $this->actingAs($bhw)
        ->post(route('archive.restore', [
            'type' => ArchiveRecordType::Announcements->value,
            'id' => $announcement->id,
        ]))
        ->assertForbidden();

    $this->assertSoftDeleted($announcement);
});

it('does not let a system admin restore patient or health records', function () {
    /** @var User $admin */
    $admin = User::factory()->systemAdmin()->createOne();
    $patient = Patient::factory()->create();
    $checkup = CheckupRecord::factory()->create();
    $immunization = ImmunizationRecord::factory()->create();
    $maternalCare = MaternalCareRecord::factory()->create();

    $records = [
        [ArchiveRecordType::Patients, $patient],
        [ArchiveRecordType::Checkups, $checkup],
        [ArchiveRecordType::Immunizations, $immunization],
        [ArchiveRecordType::MaternalCare, $maternalCare],
    ];

    foreach ($records as [$type, $record]) {
        $record->delete();

        $this->actingAs($admin)
            ->post(route('archive.restore', [
                'type' => $type->value,
                'id' => $record->id,
            ]))
            ->assertForbidden();

        $this->assertSoftDeleted($record);
    }
});
