<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function publicKey(): JsonResponse
    {
        if (! $this->pushIsConfigured()) {
            return response()->json(['enabled' => false], 503);
        }

        return response()->json([
            'enabled' => true,
            'publicKey' => config('services.webpush.public_key'),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        abort_unless($this->pushIsConfigured(), 503);

        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:4096', 'starts_with:https://'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $request->user()?->id,
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => 'aes128gcm',
            ],
        );

        return response()->json(['subscribed' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:4096', 'starts_with:https://'],
        ]);

        PushSubscription::query()
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['subscribed' => false]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $subscription = $this->findSubscription($request);

        return response()->json(['unreadCount' => $subscription?->unread_count ?? 0]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $subscription = $this->findSubscription($request);
        $subscription?->update(['unread_count' => 0]);

        return response()->json(['unreadCount' => 0]);
    }

    private function findSubscription(Request $request): ?PushSubscription
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:4096', 'starts_with:https://'],
        ]);

        return PushSubscription::query()
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->first();
    }

    private function pushIsConfigured(): bool
    {
        $publicKey = config('services.webpush.public_key');
        $privateKey = config('services.webpush.private_key');

        return is_string($publicKey)
            && preg_match('/^[A-Za-z0-9_-]{87}$/D', $publicKey) === 1
            && is_string($privateKey)
            && preg_match('/^[A-Za-z0-9_-]{43}$/D', $privateKey) === 1
            && filled(config('services.webpush.subject'));
    }
}
