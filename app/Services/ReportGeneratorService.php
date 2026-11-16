<?php

namespace App\Services;

use App\Enums\ReportType;
use App\Models\CheckupRecord;
use App\Models\HealthcareReport;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\SmsNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportGeneratorService
{
    /**
     * @return Collection<string, Collection<int, HealthcareReport>>
     */
    public function ensureMonthlyReports(): Collection
    {
        $dateFrom = now()->startOfMonth()->toDateString();
        $dateTo = now()->endOfMonth()->toDateString();
        $period = now()->format('F Y');
        $generatedBy = User::query()->where('role', 'system_admin')->value('id')
            ?? User::query()->value('id');

        foreach (ReportType::cases() as $type) {
            HealthcareReport::query()->updateOrCreate(
                [
                    'report_type' => $type,
                    'report_period' => $period,
                ],
                [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'total_patients' => $this->countForType($type, $dateFrom, $dateTo, 'patients'),
                    'total_consultations' => $this->countForType($type, $dateFrom, $dateTo, 'consultations'),
                    'total_immunizations' => $this->countForType($type, $dateFrom, $dateTo, 'immunizations'),
                    'generated_by' => $generatedBy,
                    'submission_status' => 'generated',
                ]
            );
        }

        return HealthcareReport::query()
            ->with('generatedBy')
            ->latest()
            ->get()
            ->groupBy(fn (HealthcareReport $report) => $report->report_type->value);
    }

    private function countForType(ReportType $type, string $dateFrom, string $dateTo, string $metric): int
    {
        return match ($metric) {
            'patients' => $type === ReportType::Patient
                ? Patient::query()->whereBetween('created_at', [$dateFrom, $dateTo.' 23:59:59'])->count()
                : 0,
            'consultations' => $type === ReportType::Checkup
                ? CheckupRecord::query()->whereBetween('visit_date', [$dateFrom, $dateTo])->count()
                : 0,
            'immunizations' => $type === ReportType::Immunization
                ? ImmunizationRecord::query()->whereBetween('vaccination_date', [$dateFrom, $dateTo])->count()
                : 0,
            default => 0,
        };
    }
}
