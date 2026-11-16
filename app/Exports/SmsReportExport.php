<?php

namespace App\Exports;

use App\Models\HealthcareReport;
use App\Models\SmsNotification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SmsReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private HealthcareReport $report) {}

    public function collection()
    {
        return SmsNotification::query()
            ->with('patient')
            ->whereBetween('created_at', [
                $this->report->date_from,
                $this->report->date_to->copy()->endOfDay(),
            ])
            ->get();
    }

    public function headings(): array
    {
        return ['Patient', 'Mobile Number', 'Type', 'Message', 'Status', 'Sent At'];
    }

    public function map($row): array
    {
        return [
            $row->patient?->full_name ?? 'N/A',
            $row->mobile_number,
            $row->notification_type->label(),
            $row->message,
            $row->status->label(),
            $row->sent_at?->format('Y-m-d H:i'),
        ];
    }
}
