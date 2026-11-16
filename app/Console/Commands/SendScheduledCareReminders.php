<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\User;
use App\Notifications\CheckupFollowUpScheduled;
use App\Notifications\ImmunizationNextScheduleScheduled;
use App\Notifications\MaternalCareFollowUpScheduled;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;

class SendScheduledCareReminders extends Command
{
    protected $signature = 'records:send-schedule-reminders';

    protected $description = 'Notify midwives about checkups, immunizations, and maternal care due within three days.';

    public function handle(): int
    {
        $today = today()->toDateString();
        $lastReminderDate = today()->addDays(3)->toDateString();
        $midwives = User::query()
            ->where('role', UserRole::Midwife)
            ->where('is_active', true)
            ->get();

        if ($midwives->isEmpty()) {
            return self::SUCCESS;
        }

        CheckupRecord::query()
            ->with('patient')
            ->whereDate('follow_up_date', '>=', $today)
            ->whereDate('follow_up_date', '<=', $lastReminderDate)
            ->orderBy('id')
            ->chunkById(100, function (Collection $records) use ($midwives): void {
                foreach ($records as $record) {
                    $this->notifyMidwivesOnce(
                        $midwives,
                        $record->id,
                        CheckupFollowUpScheduled::class,
                        $record->follow_up_date->toDateString(),
                        fn (): Notification => new CheckupFollowUpScheduled($record),
                    );
                }
            });

        ImmunizationRecord::query()
            ->with('patient')
            ->whereDate('next_schedule', '>=', $today)
            ->whereDate('next_schedule', '<=', $lastReminderDate)
            ->orderBy('id')
            ->chunkById(100, function (Collection $records) use ($midwives): void {
                foreach ($records as $record) {
                    $this->notifyMidwivesOnce(
                        $midwives,
                        $record->id,
                        ImmunizationNextScheduleScheduled::class,
                        $record->next_schedule->toDateString(),
                        fn (): Notification => new ImmunizationNextScheduleScheduled($record),
                    );
                }
            });

        MaternalCareRecord::query()
            ->with('patient')
            ->whereDate('follow_up_date', '>=', $today)
            ->whereDate('follow_up_date', '<=', $lastReminderDate)
            ->orderBy('id')
            ->chunkById(100, function (Collection $records) use ($midwives): void {
                foreach ($records as $record) {
                    $this->notifyMidwivesOnce(
                        $midwives,
                        $record->id,
                        MaternalCareFollowUpScheduled::class,
                        $record->follow_up_date->toDateString(),
                        fn (): Notification => new MaternalCareFollowUpScheduled($record),
                    );
                }
            });

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, User>  $midwives
     * @param  callable(): Notification  $makeNotification
     */
    private function notifyMidwivesOnce(
        Collection $midwives,
        int $recordId,
        string $notificationType,
        string $scheduledDate,
        callable $makeNotification,
    ): void {
        foreach ($midwives as $midwife) {
            $alreadyNotified = $midwife->notifications()
                ->where('type', $notificationType)
                ->where('data->record_id', $recordId)
                ->where('data->scheduled_date', $scheduledDate)
                ->exists();

            if (! $alreadyNotified) {
                $midwife->notify($makeNotification());
            }
        }
    }
}
