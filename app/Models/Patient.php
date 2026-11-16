<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'age',
        'gender',
        'civil_status',
        'contact_number',
        'email',
        'address',
        'blood_type',
        'emergency_contact_person',
        'emergency_contact_relationship',
        'emergency_contact_number',
        'allergies',
        'existing_conditions',
        'notes',
        'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
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

    public function smsNotifications(): HasMany
    {
        return $this->hasMany(SmsNotification::class);
    }

    public function getFullNameAttribute(): string
    {
        $name = trim(preg_replace('/\s+/', ' ', "{$this->first_name} {$this->middle_name} {$this->last_name}"));

        if ($this->suffix) {
            $name .= ", {$this->suffix}";
        }

        return $name;
    }

    public function computeAge(): int
    {
        return (int) $this->birth_date->age;
    }
}
