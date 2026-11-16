<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaternalCareRecord extends Model
{
    /** @use HasFactory<\Database\Factories\MaternalCareRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'user_id',
        'pregnancy_number',
        'para',
        'trimester',
        'gestational_weeks',
        'gestational_days',
        'expected_delivery_date',
        'visit_date',
        'follow_up_date',
        'last_menstrual_period',
        'spouse_name',
        'living_children',
        'birth_plan',
        'hospital',
        'rhu',
        'lgc',
        'birth_attendant',
        'weight',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'height',
        'present_history',
        'present_history_other',
        'past_history',
        'past_history_other',
        'family_history',
        'family_history_other',
        'past_health_history',
        'past_health_history_other',
        'conjunctiva_findings',
        'neck_findings',
        'breast_findings',
        'abdomen_findings',
        'vaginal_findings',
        'vaginal_findings_other',
        'findings',
        'recommendations',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery_date' => 'date',
            'visit_date' => 'date',
            'follow_up_date' => 'date',
            'last_menstrual_period' => 'date',
            'temperature' => 'decimal:1',
            'height' => 'decimal:1',
            'present_history' => 'array',
            'past_history' => 'array',
            'family_history' => 'array',
            'past_health_history' => 'array',
            'conjunctiva_findings' => 'array',
            'neck_findings' => 'array',
            'breast_findings' => 'array',
            'abdomen_findings' => 'array',
            'vaginal_findings' => 'array',
        ];
    }

    public function getTrimesterPeriodAttribute(): ?string
    {
        if ($this->trimester) {
            return $this->trimester;
        }

        $weeks = $this->gestational_weeks;

        if ($weeks === null && $this->last_menstrual_period && $this->visit_date) {
            $days = (int) $this->last_menstrual_period->diffInDays($this->visit_date);
            $weeks = intdiv($days, 7);
        }

        if ($weeks === null) {
            return null;
        }

        return match (true) {
            $weeks < 14 => '1st Trimester (1–13 weeks)',
            $weeks < 28 => '2nd Trimester (14–27 weeks)',
            default => '3rd Trimester (28+ weeks)',
        };
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
