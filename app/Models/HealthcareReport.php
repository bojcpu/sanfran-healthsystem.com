<?php

namespace App\Models;

use App\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthcareReport extends Model
{
    /** @use HasFactory<\Database\Factories\HealthcareReportFactory> */
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_period',
        'date_from',
        'date_to',
        'total_patients',
        'total_consultations',
        'total_immunizations',
        'generated_by',
        'submission_status',
    ];

    protected function casts(): array
    {
        return [
            'report_type' => ReportType::class,
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
