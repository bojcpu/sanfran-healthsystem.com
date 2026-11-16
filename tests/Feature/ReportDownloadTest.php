<?php

use App\Enums\ReportType;
use App\Models\HealthcareReport;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;

it('downloads a populated patient report as a PDF', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $report = HealthcareReport::query()->create([
        'report_type' => ReportType::Patient,
        'report_period' => now()->format('F Y'),
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->endOfMonth()->toDateString(),
        'generated_by' => $user->id,
        'submission_status' => 'generated',
    ]);
    Patient::factory()->createOne(['registered_by' => $user->id]);

    $this->actingAs($user)
        ->get(route('reports.pdf', $report))
        ->assertOk()
        ->assertDownload('sanfran-patient-report.pdf');
});

it('downloads a populated patient report as an Excel workbook', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $report = HealthcareReport::query()->create([
        'report_type' => ReportType::Patient,
        'report_period' => now()->format('F Y'),
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->endOfMonth()->toDateString(),
        'generated_by' => $user->id,
        'submission_status' => 'generated',
    ]);
    Patient::factory()->createOne(['registered_by' => $user->id]);

    $this->actingAs($user)
        ->get(route('reports.excel', $report))
        ->assertDownload('sanfran-patient-report.xlsx');
});

it('previews maternal care records using the module fields and count', function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $report = HealthcareReport::query()->create([
        'report_type' => ReportType::MaternalCare,
        'report_period' => now()->format('F Y'),
        'date_from' => now()->startOfMonth()->toDateString(),
        'date_to' => now()->endOfMonth()->toDateString(),
        'generated_by' => $user->id,
        'submission_status' => 'generated',
    ]);
    $record = MaternalCareRecord::factory()->createOne([
        'visit_date' => now()->toDateString(),
        'trimester' => '2nd Trimester',
        'follow_up_date' => now()->addWeek()->toDateString(),
        'expected_delivery_date' => now()->addMonths(3)->toDateString(),
        'blood_pressure' => '110/70',
        'weight' => '60 kg',
    ]);
    $record->patient->delete();

    $this->actingAs($user)
        ->get(route('reports.preview', $report))
        ->assertOk()
        ->assertSee('Maternal Care Records')
        ->assertSee('Trimester Period')
        ->assertSee('Follow-up')
        ->assertSee('Patient record unavailable')
        ->assertSee('110/70')
        ->assertSee('60 kg')
        ->assertDontSee('Consultations')
        ->assertDontSee('Immunizations');
});