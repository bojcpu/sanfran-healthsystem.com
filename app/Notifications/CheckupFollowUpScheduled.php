<?php

namespace App\Notifications;

use App\Models\CheckupRecord;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CheckupFollowUpScheduled extends Notification
{
    use Queueable;

    public function __construct(private CheckupRecord $record) {}

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

        $followUpDate = $this->record->follow_up_date?->copy()->startOfDay();
        $daysUntilFollowUp = $followUpDate ? (int) Carbon::today()->startOfDay()->diffInDays($followUpDate, false) : 0;
        $patientName = $this->record->patient?->full_name ?? 'Patient';
        $message = match (true) {
            $daysUntilFollowUp === 0 => 'Checkup follow-up for '.$patientName.' is due today.',
            $daysUntilFollowUp === 1 => 'Checkup follow-up for '.$patientName.' is due in 1 day.',
            default => 'Checkup follow-up for '.$patientName.' is due in '.$daysUntilFollowUp.' days.',
        };

        return [
            'record_id' => $this->record->id,
            'source_type' => CheckupRecord::class,
            'source_id' => $this->record->id,
            'patient_name' => $patientName,
            'follow_up_date' => $this->record->follow_up_date?->toDateString() ?? '',
            'scheduled_date' => $this->record->follow_up_date?->toDateString() ?? '',
            'date_label' => 'Follow-up date',
            'days_until_follow_up' => $daysUntilFollowUp,
            'message' => $message,
            'url' => route('checkups.index'),
            'action_label' => 'View Checkups',
        ];
    }
}
