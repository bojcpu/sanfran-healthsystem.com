<?php

namespace App\Exports;

use App\Models\HealthcareReport;
use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PatientReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private HealthcareReport $report) {}

    public function collection()
    {
        return Patient::query()
            ->whereBetween('created_at', [
                $this->report->date_from,
                $this->report->date_to->copy()->endOfDay(),
            ])
            ->get();
    }

    public function headings(): array
    {
        return ['Patient Code', 'Full Name', 'Gender', 'Age', 'Contact', 'Address', 'Registered At'];
    }

    public function map($patient): array
    {
        return [
            $patient->patient_code,
            $patient->full_name,
            $patient->gender,
            $patient->age,
            $patient->contact_number,
            $patient->address,
            $patient->created_at->format('Y-m-d'),
        ];
    }
}
