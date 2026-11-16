<?php

use App\Enums\SmsNotificationType;
use App\Enums\SmsStatus;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\SmsNotification;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends messages through the iProg SMS API', function () {
    config(['services.iprog.api_token' => 'test-api-token']);

    Http::fake([
        'https://www.iprogsms.com/api/v1/sms_messages' => Http::response([
            'status' => 200,
            'message' => 'Queued',
            'message_id' => 'test-message-id',
        ]),
    ]);

    $sender = User::factory()->create();
    $notification = app(SmsService::class)->send(
        '09171234567',
        'Test reminder',
        SmsNotificationType::Custom,
        $sender,
    );

    expect($notification->status)->toBe(SmsStatus::Sent)
        ->and($notification->mobile_number)->toBe('+639171234567');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://www.iprogsms.com/api/v1/sms_messages'
        && $request['api_token'] === 'test-api-token'
        && $request['phone_number'] === '639171234567'
        && $request['message'] === 'Test reminder');
});

it('marks the notification failed when iProg rejects the message', function () {
    config(['services.iprog.api_token' => 'test-api-token']);

    Http::fake([
        'https://www.iprogsms.com/api/v1/sms_messages' => Http::response([
            'status' => 422,
            'message' => 'Insufficient credits',
        ]),
    ]);

    $sender = User::factory()->create();
    $notification = app(SmsService::class)->send(
        '09171234567',
        'Test reminder',
        SmsNotificationType::Custom,
        $sender,
    );

    expect($notification->status)->toBe(SmsStatus::Failed)
        ->and($notification->delivery_response)->toContain('Insufficient credits');
});

it('sends edited patient reminders to SMS history and returns to the selected module', function () {
    config(['services.iprog.api_token' => 'test-api-token']);

    Http::fake([
        'https://www.iprogsms.com/api/v1/sms_messages' => Http::response([
            'status' => 200,
            'message' => 'Queued',
        ]),
    ]);

    $sender = User::factory()->midwife()->create();
    $patient = Patient::factory()->create([
        'first_name' => 'Maria',
        'last_name' => 'Dela Cruz',
        'contact_number' => '09171234567',
    ]);
    $this->actingAs($sender)
        ->post(route('sms.store'), [
            'patient_id' => $patient->id,
            'mobile_number' => $patient->contact_number,
            'notification_type' => SmsNotificationType::CheckupReminder->value,
            'message' => 'Edited checkup reminder',
            'return_to' => 'checkups.index',
        ])
        ->assertRedirect(route('checkups.index'))
        ->assertSessionHas('success');

    $this->post(route('sms.store'), [
        'patient_id' => $patient->id,
        'mobile_number' => $patient->contact_number,
        'notification_type' => SmsNotificationType::ImmunizationReminder->value,
        'message' => 'Edited immunization reminder',
        'return_to' => 'immunizations.index',
    ])
        ->assertRedirect(route('immunizations.index'))
        ->assertSessionHas('success');

    $this->post(route('sms.store'), [
        'patient_id' => $patient->id,
        'mobile_number' => $patient->contact_number,
        'notification_type' => SmsNotificationType::MaternalCareReminder->value,
        'message' => 'Edited maternal care reminder',
        'return_to' => 'maternal-care.index',
    ])
        ->assertRedirect(route('maternal-care.index'))
        ->assertSessionHas('success');

    expect(SmsNotification::query()->count())->toBe(3)
        ->and(SmsNotification::query()->where('notification_type', SmsNotificationType::CheckupReminder)->value('message'))
        ->toBe('Edited checkup reminder')
        ->and(SmsNotification::query()->where('notification_type', SmsNotificationType::ImmunizationReminder)->value('message'))
        ->toBe('Edited immunization reminder')
        ->and(SmsNotification::query()->where('notification_type', SmsNotificationType::MaternalCareReminder)->value('message'))
        ->toBe('Edited maternal care reminder');
});

it('shows send buttons and the editable message modal for records without a schedule or saved phone', function () {
    $sender = User::factory()->midwife()->create();
    $patient = Patient::factory()->create(['contact_number' => null]);
    CheckupRecord::factory()->create(['patient_id' => $patient->id, 'follow_up_date' => null]);
    ImmunizationRecord::factory()->create(['patient_id' => $patient->id, 'next_schedule' => null]);
    MaternalCareRecord::factory()->create(['patient_id' => $patient->id, 'follow_up_date' => null]);

    $this->actingAs($sender)
        ->get(route('checkups.index'))
        ->assertSuccessful()
        ->assertSee('Send SMS')
        ->assertSee('record-sms-mobile-number')
        ->assertSee($patient->full_name);

    $this->get(route('immunizations.index'))
        ->assertSuccessful()
        ->assertSee('Send SMS')
        ->assertSee('record-sms-mobile-number');

    $this->get(route('maternal-care.index'))
        ->assertSuccessful()
        ->assertSee('Send SMS')
        ->assertSee('record-sms-mobile-number');
});

it('restricts record reminder sending to SMS-authorized staff', function () {
    $patient = Patient::factory()->create();
    $checkup = CheckupRecord::factory()->create([
        'patient_id' => $patient->id,
        'follow_up_date' => today()->addDays(3)->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('sms.store'), [
            'patient_id' => $patient->id,
            'mobile_number' => $patient->contact_number,
            'notification_type' => SmsNotificationType::CheckupReminder->value,
            'message' => 'Unauthorized reminder',
            'return_to' => 'checkups.index',
        ])
        ->assertForbidden();

    expect(SmsNotification::query()->count())->toBe(0);
});
