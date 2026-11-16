<?php

namespace App\Notifications;

use App\Models\ImmunizationRecord;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImmunizationNextScheduleScheduled extends Notification
{
    use Queueable;

    public function __construct(private ImmunizationRecord $record) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        $this->record->loadMissing('patient');

        $scheduledDate = $this->record->next_schedule?->copy()->startOfDay();
        $daysUntilSchedule = $scheduledDate ? (int) Carbon::today()->startOfDay()->diffInDays($scheduledDate, false) : 0;
        $patientName = $this->record->patient?->full_name ?? 'Patient';
        $vaccineName = $this->record->vaccine_name;
        $message = match (true) {
            $daysUntilSchedule === 0 => $vaccineName.' for '.$patientName.' is scheduled for today.',
            $daysUntilSchedule === 1 => $vaccineName.' for '.$patientName.' is scheduled in 1 day.',
            default => $vaccineName.' for '.$patientName.' is scheduled in '.$daysUntilSchedule.' days.',
        };

        return [
            'record_id' => $this->record->id,
            'source_type' => ImmunizationRecord::class,
            'source_id' => $this->record->id,
            'patient_name' => $patientName,
            'vaccine_name' => $vaccineName,
            'next_schedule' => $this->record->next_schedule?->toDateString() ?? '',
            'scheduled_date' => $this->record->next_schedule?->toDateString() ?? '',
            'date_label' => 'Next schedule',
            'days_until_schedule' => $daysUntilSchedule,
            'message' => $message,
            'url' => route('immunizations.index'),
            'action_label' => 'View Immunizations',
        ];
    }
}
