<?php

namespace App\Enums;

enum ReportType: string
{
    case Patient = 'patient';
    case Checkup = 'checkup';
    case Immunization = 'immunization';
    case MaternalCare = 'maternal_care';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Patient => 'Patient Report',
            self::Checkup => 'Checkup Report',
            self::Immunization => 'Immunization Report',
            self::MaternalCare => 'Maternal Care Report',
            self::Sms => 'SMS Report',
        };
    }
}
