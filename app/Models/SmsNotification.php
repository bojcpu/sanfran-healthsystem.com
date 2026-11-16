<?php

namespace App\Models;

use App\Enums\SmsNotificationType;
use App\Enums\SmsStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsNotification extends Model
{
    /** @use HasFactory<\Database\Factories\SmsNotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'mobile_number',
        'message',
        'notification_type',
        'status',
        'sent_by',
        'sent_at',
        'delivery_response',
    ];

    protected function casts(): array
    {
        return [
            'notification_type' => SmsNotificationType::class,
            'status' => SmsStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
