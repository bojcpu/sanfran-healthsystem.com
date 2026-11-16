<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class AnnouncementPushService
{
    public function send(Announcement $announcement): void
    {
        $vapid = [
            'subject' => config('services.webpush.subject'),
            'publicKey' => config('services.webpush.public_key'),
            'privateKey' => config('services.webpush.private_key'),
        ];

        if (collect($vapid)->contains(fn ($value) => blank($value))) {
            return;
        }

        try {
            $subscriptions = PushSubscription::query()->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            $webPush = new WebPush(['VAPID' => $vapid], [], 10);

            foreach ($subscriptions as $pushSubscription) {
                $pushSubscription->increment('unread_count');
                $unreadCount = $pushSubscription->fresh()->unread_count;

                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $pushSubscription->endpoint,
                        'keys' => [
                            'p256dh' => $pushSubscription->public_key,
                            'auth' => $pushSubscription->auth_token,
                        ],
                        'contentEncoding' => $pushSubscription->content_encoding,
                    ]),
                    json_encode([
                        'announcementId' => $announcement->id,
                        'title' => $announcement->title,
                        'body' => Str::limit($announcement->content, 180),
                        'url' => route('home', ['announcement' => $announcement->id]),
                        'unreadCount' => $unreadCount,
                    ], JSON_THROW_ON_ERROR),
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::query()
                        ->where('endpoint_hash', hash('sha256', $report->getEndpoint()))
                        ->delete();
                } elseif (! $report->isSuccess()) {
                    Log::warning('Announcement push delivery failed.', [
                        'endpoint' => $report->getEndpoint(),
                        'reason' => $report->getReason(),
                    ]);
                }
            }
        } catch (Throwable $exception) {
            Log::error('Announcement push delivery could not be completed.', [
                'announcement_id' => $announcement->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
