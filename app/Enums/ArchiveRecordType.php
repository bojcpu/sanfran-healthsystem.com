<?php

namespace App\Enums;

use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

enum ArchiveRecordType: string
{
    case Patients = 'patients';
    case Checkups = 'checkups';
    case Immunizations = 'immunizations';
    case MaternalCare = 'maternal-care';
    case Announcements = 'announcements';

    public function label(): string
    {
        return match ($this) {
            self::Patients => 'Patients',
            self::Checkups => 'Checkups',
            self::Immunizations => 'Immunizations',
            self::MaternalCare => 'Maternal Care',
            self::Announcements => 'Announcements',
        };
    }

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Patients => Patient::class,
            self::Checkups => CheckupRecord::class,
            self::Immunizations => ImmunizationRecord::class,
            self::MaternalCare => MaternalCareRecord::class,
            self::Announcements => Announcement::class,
        };
    }

    public function module(): string
    {
        return match ($this) {
            self::Patients => 'Patient Management',
            self::Checkups => 'Checkup Records',
            self::Immunizations => 'Immunization Records',
            self::MaternalCare => 'Maternal Care Records',
            self::Announcements => 'Announcements',
        };
    }

    public function isAllowedFor(UserRole $role): bool
    {
        return match ($this) {
            self::Announcements => $role->canArchiveAnnouncements(),
            default => $role->canArchiveRecords(),
        };
    }

    public function canRestoreFor(UserRole $role): bool
    {
        return $this->isAllowedFor($role)
            && ! ($role === UserRole::SystemAdmin && $this !== self::Announcements)
            && ! ($this === self::MaternalCare && $role === UserRole::Bhw);
    }

    /**
     * @return list<self>
     */
    public static function visibleFor(UserRole $role): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $type) => $type->isAllowedFor($role),
        ));
    }
}
