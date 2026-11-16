<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'profile_picture',
        'password',
        'role',
        'is_active',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    public function checkupRecords(): HasMany
    {
        return $this->hasMany(CheckupRecord::class);
    }

    public function immunizationRecords(): HasMany
    {
        return $this->hasMany(ImmunizationRecord::class);
    }

    public function maternalCareRecords(): HasMany
    {
        return $this->hasMany(MaternalCareRecord::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'posted_by');
    }

    public function smsNotifications(): HasMany
    {
        return $this->hasMany(SmsNotification::class, 'sent_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function healthcareReports(): HasMany
    {
        return $this->hasMany(HealthcareReport::class, 'generated_by');
    }

    public function registeredPatients(): HasMany
    {
        return $this->hasMany(Patient::class, 'registered_by');
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class)->latest();
    }

    public function isSystemAdmin(): bool
    {
        return $this->role === UserRole::SystemAdmin;
    }

    public function isMidwife(): bool
    {
        return $this->role === UserRole::Midwife;
    }

    public function isBhw(): bool
    {
        return $this->role === UserRole::Bhw;
    }

    public function canManageUsers(): bool
    {
        return $this->role->canManageUsers();
    }

    public function assignableRoles(): array
    {
        return $this->role->assignableRoles();
    }

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function profilePictureUrl(): ?string
    {
        return $this->profile_picture
            ? asset('storage/'.$this->profile_picture)
            : null;
    }

    public function initials(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }
}
