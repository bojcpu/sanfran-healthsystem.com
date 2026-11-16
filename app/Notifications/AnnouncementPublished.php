<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AnnouncementPublished extends Notification
{
    use Queueable;

    public function __construct(private Announcement $announcement) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'source_type' => Announcement::class,
            'source_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'message' => 'New public announcement: '.$this->announcement->title,
            'date_label' => 'Publish date',
            'scheduled_date' => $this->announcement->publish_date->toDateString(),
            'url' => route('home'),
            'action_label' => 'View announcement',
        ];
    }
}
