<?php

namespace App\Exports;

use App\Models\CheckupRecord;
use App\Models\HealthcareReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CheckupReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private HealthcareReport $report) {}

    public function collection()
    {
        return CheckupRecord::query()
            ->with('patient')
            ->whereBetween('visit_date', [$this->report->date_from, $this->report->date_to])
            ->get();
    }

    public function headings(): array
    {
        return ['Patient', 'Visit Date', 'Complaint', 'Diagnosis', 'Treatment', 'Prescription', 'Follow-up'];
    }

    public function map($row): array
    {
        return [
            $row->patient->full_name,
            $row->visit_date->format('Y-m-d'),
            $row->complaint,
            $row->diagnosis,
            $row->treatment,
            $row->prescription,
            $row->follow_up_date?->format('Y-m-d'),
        ];
    }
}
