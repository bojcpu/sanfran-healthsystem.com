<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmunizationRecord extends Model
{
    /** @use HasFactory<\Database\Factories\ImmunizationRecordFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'user_id',
        'vaccine_name',
        'dose',
        'vaccination_date',
        'next_schedule',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'vaccination_date' => 'date',
            'next_schedule' => 'date',
        ];
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
