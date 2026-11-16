<?php

namespace App\Exports;

use App\Models\HealthcareReport;
use App\Models\ImmunizationRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ImmunizationReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private HealthcareReport $report) {}

    public function collection()
    {
        return ImmunizationRecord::query()
            ->with('patient')
            ->whereBetween('vaccination_date', [$this->report->date_from, $this->report->date_to])
            ->get();
    }

    public function headings(): array
    {
        return ['Patient', 'Vaccine', 'Dose', 'Vaccination Date', 'Next Schedule', 'Remarks'];
    }

    public function map($row): array
    {
        return [
            $row->patient->full_name,
            $row->vaccine_name,
            $row->dose,
            $row->vaccination_date->format('Y-m-d'),
            $row->next_schedule?->format('Y-m-d'),
            $row->remarks,
        ];
    }
}
