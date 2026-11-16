<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'since_id' => ['sometimes', 'integer', 'min:0'],
        ]);

        $publicAnnouncements = Announcement::query()->active();
        $sinceId = (int) ($data['since_id'] ?? 0);
        $latestId = (clone $publicAnnouncements)->max('id') ?? 0;
        $latestUnreadId = (clone $publicAnnouncements)
            ->where('id', '>', $sinceId)
            ->max('id');

        return response()->json([
            'unreadCount' => (clone $publicAnnouncements)
                ->where('id', '>', $sinceId)
                ->count('id'),
            'latestId' => $latestId,
            'latestUnreadUrl' => $latestUnreadId
                ? route('home', ['announcement' => $latestUnreadId])
                : null,
            'latestUrl' => $latestId
                ? route('home', ['announcement' => $latestId])
                : route('home'),
        ]);
    }

    public function __invoke(Request $request): View
    {
        $currentType = AnnouncementType::tryFrom((string) $request->query('type'));
        $currentSort = $request->query('sort') === 'oldest' ? 'oldest' : 'latest';

        $announcements = Announcement::query()
            ->active()
            ->with('postedBy')
            ->when($currentType, fn ($query) => $query->where('type', $currentType->value))
            ->when(
                $currentSort === 'oldest',
                fn ($query) => $query->orderBy('created_at')->orderBy('id'),
                fn ($query) => $query->orderByDesc('created_at')->orderByDesc('id'),
            )
            ->get();

        $announcementsByType = $announcements
            ->groupBy(fn (Announcement $announcement) => $announcement->type->value);

        if ($currentType === null) {
            $announcementsByType = $announcementsByType
                ->map(fn ($moduleAnnouncements) => $moduleAnnouncements->take(1));
        }

        $announcementsByType = $announcementsByType
            ->sortKeysUsing(function (string $left, string $right): int {
                return AnnouncementType::from($left)->sortOrder() <=> AnnouncementType::from($right)->sortOrder();
            });

        $openAnnouncementId = (int) $request->query('announcement', 0);
        $openAnnouncement = $openAnnouncementId > 0
            ? Announcement::query()->active()->with('postedBy')->find($openAnnouncementId)
            : null;

        if ($openAnnouncement) {
            $typeValue = $openAnnouncement->type->value;
            $announcementsByType[$typeValue] = collect([$openAnnouncement])
                ->merge($announcementsByType->get($typeValue, collect())->reject(
                    fn (Announcement $announcement) => $announcement->id === $openAnnouncement->id,
                ))
                ->values();
        }

        return view('landing', [
            'announcementsByType' => $announcementsByType,
            'types' => AnnouncementType::cases(),
            'currentType' => $currentType,
            'currentSort' => $currentSort,
            'openAnnouncementId' => $openAnnouncement?->id,
        ]);
    }
}
