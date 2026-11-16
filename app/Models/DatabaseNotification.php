<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;
use InvalidArgumentException;

class DatabaseNotification extends BaseDatabaseNotification
{
    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            $data = $notification->data;
            $hasSourceType = array_key_exists('source_type', $data);
            $hasSourceId = array_key_exists('source_id', $data);

            if (! $hasSourceType && ! $hasSourceId) {
                return;
            }

            $sourceType = $data['source_type'] ?? null;
            $sourceId = $data['source_id'] ?? null;
            $sourceColumns = [
                Announcement::class => 'announcement_id',
                CheckupRecord::class => 'checkup_record_id',
                ImmunizationRecord::class => 'immunization_record_id',
                MaternalCareRecord::class => 'maternal_care_record_id',
            ];

            if (! is_string($sourceType)
                || ! array_key_exists($sourceType, $sourceColumns)
                || ! is_int($sourceId)
                || $sourceId < 1) {
                throw new InvalidArgumentException('Notification source metadata must reference a supported record.');
            }

            $notification->{$sourceColumns[$sourceType]} = $sourceId;

            unset($data['source_type'], $data['source_id']);
            $notification->data = $data;
        });
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function checkupRecord(): BelongsTo
    {
        return $this->belongsTo(CheckupRecord::class);
    }

    public function immunizationRecord(): BelongsTo
    {
        return $this->belongsTo(ImmunizationRecord::class);
    }

    public function maternalCareRecord(): BelongsTo
    {
        return $this->belongsTo(MaternalCareRecord::class);
    }

    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSourceAttribute(): ?Model
    {
        return match (true) {
            $this->announcement_id !== null => $this->announcement,
            $this->checkup_record_id !== null => $this->checkupRecord,
            $this->immunization_record_id !== null => $this->immunizationRecord,
            $this->maternal_care_record_id !== null => $this->maternalCareRecord,
            default => null,
        };
    }
}
