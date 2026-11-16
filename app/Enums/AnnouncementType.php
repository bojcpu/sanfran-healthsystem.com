<?php

namespace App\Enums;

enum AnnouncementType: string
{
    case Emergency = 'emergency';
    case Advisory = 'advisory';
    case Event = 'event';
    case Immunization = 'immunization';
    case MaternalCare = 'maternal_care';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Emergency => 'Emergency',
            self::Advisory => 'Health Advisory',
            self::Event => 'Event',
            self::Immunization => 'Immunization',
            self::MaternalCare => 'Maternal Care',
            self::General => 'General',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Emergency => 0,
            self::Advisory => 1,
            self::Event => 2,
            self::Immunization => 3,
            self::MaternalCare => 4,
            self::General => 5,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Emergency => 'badge-danger',
            self::Advisory => 'badge-warning',
            self::Event => 'badge-info',
            self::Immunization => 'badge-success',
            self::MaternalCare => 'badge badge-maternal',
            self::General => 'badge badge-general',
        };
    }

    public function iconClass(): string
    {
        return match ($this) {
            self::Emergency => 'announcement-type-emergency',
            self::Advisory => 'announcement-type-advisory',
            self::Event => 'announcement-type-event',
            self::Immunization => 'announcement-type-immunization',
            self::MaternalCare => 'announcement-type-maternal-care',
            self::General => 'announcement-type-general',
        };
    }

    public function chipClass(): string
    {
        return 'filter-chip-'.$this->value;
    }
}
