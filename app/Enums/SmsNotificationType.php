<?php

namespace App\Enums;

enum SmsNotificationType: string
{
    case CheckupReminder = 'checkup_reminder';
    case ImmunizationReminder = 'immunization_reminder';
    case MaternalCareReminder = 'maternal_care_reminder';
    case Advisory = 'advisory';
    case Announcement = 'announcement';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::CheckupReminder => 'Checkup Reminder',
            self::ImmunizationReminder => 'Immunization Reminder',
            self::MaternalCareReminder => 'Maternal Care Reminder',
            self::Advisory => 'Health Advisory',
            self::Announcement => 'Announcement',
            self::Custom => 'Custom Message',
        };
    }
}
