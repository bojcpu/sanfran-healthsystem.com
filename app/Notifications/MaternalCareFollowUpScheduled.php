<?php

namespace App\Notifications;

use App\Models\MaternalCareRecord;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaternalCareFollowUpScheduled extends Notification
{
    use Queueable;

    public function __construct(private MaternalCareRecord $record) {}

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

        $followUpDate = $this->record->follow_up_date ? Carbon::parse($this->record->follow_up_date)->startOfDay() : null;
        $daysUntilFollowUp = $followUpDate ? (int) Carbon::today()->startOfDay()->diffInDays($followUpDate, false) : 0;
        $patientName = $this->record->patient?->full_name ?? 'Patient';
        $message = match (true) {
            $daysUntilFollowUp === 0 => 'Prenatal follow-up for '.$patientName.' is due today.',
            $daysUntilFollowUp === 1 => 'Prenatal follow-up for '.$patientName.' is due in 1 day.',
            default => 'Prenatal follow-up for '.$patientName.' is due in '.$daysUntilFollowUp.' days.',
        };

        return [
            'record_id' => $this->record->id,
            'source_type' => MaternalCareRecord::class,
            'source_id' => $this->record->id,
            'patient_name' => $patientName,
            'follow_up_date' => $this->record->follow_up_date?->toDateString() ?? '',
            'scheduled_date' => $this->record->follow_up_date?->toDateString() ?? '',
            'days_until_follow_up' => $daysUntilFollowUp,
            'message' => $message,
            'url' => route('maternal-care.index'),
        ];
    }
}
