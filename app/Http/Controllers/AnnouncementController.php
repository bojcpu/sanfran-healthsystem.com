<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementType;
use App\Enums\AnnouncementStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use App\Services\AnnouncementPushService;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private AnnouncementPushService $announcementPush,
    ) {}

    public function index(Request $request): View
    {
        $announcementsByType = Announcement::query()
            ->with('postedBy')
            ->when(! $request->filled('highlight') && $request->search, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($request->filled('highlight'), fn ($q) => $q->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$request->integer('highlight')]))
            ->orderByDesc('publish_date')
            ->get()
            ->groupBy(fn (Announcement $announcement) => $announcement->type->value);

        $typeOrder = collect(AnnouncementType::cases())
            ->mapWithKeys(fn (AnnouncementType $type) => [$type->value => $type->label()]);

        return view('announcements.index', compact('announcementsByType', 'typeOrder'));
    }

    public function create(): RedirectResponse
    {
        $this->authorizeAnnouncementManagement();

        return redirect()->route('announcements.index');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validated();
        $data['posted_by'] = $user->id;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::query()->create($data);

        $today = now()->toDateString();
        $isPublic = $announcement->status === AnnouncementStatus::Active
            && $announcement->publish_date->toDateString() <= $today
            && ($announcement->expiry_date === null || $announcement->expiry_date->toDateString() >= $today);

        if ($isPublic) {
            User::query()
                ->where('role', UserRole::Midwife)
                ->where('is_active', true)
                ->get()
                ->each(fn (User $midwife) => $midwife->notify(new AnnouncementPublished($announcement)));

            $this->announcementPush->send($announcement);
        }

        $this->activityLog->log('create', "Published announcement: {$announcement->title}.", 'Announcements');

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorizeAnnouncementManagement();

        return view('announcements.edit', compact('announcement'));
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($data);

        $this->activityLog->log('update', "Updated announcement: {$announcement->title}.", 'Announcements');

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorizeAnnouncementManagement();

        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }

        $title = $announcement->title;
        Announcement::query()->whereKey($announcement->getKey())->delete();

        $this->activityLog->log('archive', "Archived announcement: {$title}.", 'Announcements');

        return redirect()->route('announcements.index')->with('success', 'Announcement archived successfully.');
    }

    private function authorizeAnnouncementManagement(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user?->hasRole(UserRole::SystemAdmin, UserRole::Midwife)) {
            abort(403);
        }
    }
}
