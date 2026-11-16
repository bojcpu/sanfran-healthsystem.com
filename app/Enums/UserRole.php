<?php

namespace App\Enums;

enum UserRole: string
{
    case SystemAdmin = 'system_admin';
    case Midwife = 'midwife';
    case Bhw = 'bhw';

    public function label(): string
    {
        return match ($this) {
            self::SystemAdmin => 'System Admin',
            self::Midwife => 'Midwife',
            self::Bhw => 'Barangay Health Worker',
        };
    }

    public function canManageUsers(): bool
    {
        return $this === self::SystemAdmin;
    }

    public function canViewUsers(): bool
    {
        return in_array($this, [self::SystemAdmin, self::Midwife], true);
    }

    public function canManagePatients(): bool
    {
        return in_array($this, [self::SystemAdmin, self::Midwife, self::Bhw], true);
    }

    public function canDeletePatients(): bool
    {
        return $this->canArchiveRecords();
    }

    public function canArchiveRecords(): bool
    {
        return in_array($this, [self::SystemAdmin, self::Midwife, self::Bhw], true);
    }

    public function canArchiveAnnouncements(): bool
    {
        return in_array($this, [self::SystemAdmin, self::Midwife], true);
    }

    public function canViewArchive(): bool
    {
        return $this->canArchiveRecords();
    }

    public function canViewActivityLogs(): bool
    {
        return in_array($this, [self::SystemAdmin, self::Midwife], true);
    }

    /**
     * @return list<self>
     */
    public function assignableRoles(): array
    {
        return match ($this) {
            self::SystemAdmin => [self::Midwife, self::Bhw],
            self::Midwife => [self::Bhw],
            self::Bhw => [],
        };
    }

    public function canManageRole(self $role): bool
    {
        return in_array($role, $this->assignableRoles(), true);
    }

    public function canManageUser(UserRole $targetRole): bool
    {
        return match ($this) {
            self::SystemAdmin => in_array($targetRole, [self::SystemAdmin, self::Midwife, self::Bhw], true),
            self::Midwife => false,
            self::Bhw => false,
        };
    }
}
