<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isMidwife(), 403);

        $this->runScheduledCareReminderCheck();

        return response()->json([
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isMidwife(), 403);

        $this->runScheduledCareReminderCheck();

        $notifications = $user->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isMidwife(), 403);

        $user->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return redirect()->route('notifications.index');
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isMidwife(), 403);

        $notification = $user->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        $source = $notification->source;
        $destination = match (true) {
            $source instanceof MaternalCareRecord => route('maternal-care.index', ['highlight' => $source->id])
                .'#maternal-care-record-'.$source->id,
            $source instanceof CheckupRecord => route('checkups.index', ['highlight' => $source->id])
                .'#checkup-record-'.$source->id,
            $source instanceof ImmunizationRecord => route('immunizations.index', ['highlight' => $source->id])
                .'#immunization-record-'.$source->id,
            $source instanceof Announcement => route('announcements.index', ['highlight' => $source->id])
                .'#announcement-'.$source->id,
            default => route('notifications.index'),
        };

        return redirect()->to($destination);
    }

    private function runScheduledCareReminderCheck(): void
    {
        $cacheKey = 'scheduled-care-reminders:'.now()->format('YmdHi');

        if (Cache::add($cacheKey, true, now()->addMinutes(2))) {
            Artisan::call('records:send-schedule-reminders');
        }
    }
}
