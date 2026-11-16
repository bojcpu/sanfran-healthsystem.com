<?php

namespace App\Exports;

use App\Models\HealthcareReport;
use App\Models\MaternalCareRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaternalCareReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private HealthcareReport $report) {}

    public function collection()
    {
        return MaternalCareRecord::query()
            ->with('patient')
            ->whereBetween('visit_date', [$this->report->date_from, $this->report->date_to])
            ->get();
    }

    public function headings(): array
    {
        return ['Patient', 'Trimester Period', 'Visit Date', 'Follow-up', 'BP', 'Weight', 'EDD'];
    }

    public function map($row): array
    {
        return [
            $row->patient?->full_name ?? 'Patient record unavailable',
            $row->trimester_period ?? '—',
            $row->visit_date->format('M d, Y'),
            $row->follow_up_date?->format('M d, Y') ?? '—',
            $row->blood_pressure ?? '—',
            $row->weight ?? '—',
            $row->expected_delivery_date?->format('M d, Y') ?? '—',
        ];
    }
}
