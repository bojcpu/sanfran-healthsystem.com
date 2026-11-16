<?php

namespace App\Http\Controllers;

use App\Enums\AnnouncementStatus;
use App\Enums\SmsStatus;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\SmsNotification;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'patients' => Patient::query()->count(),
            'checkups' => CheckupRecord::query()->whereMonth('visit_date', now()->month)->count(),
            'immunizations' => ImmunizationRecord::query()->count(),
            'maternal_care' => MaternalCareRecord::query()->count(),
            'announcements' => Announcement::query()->count(),
            'sms_sent' => SmsNotification::query()->where('status', SmsStatus::Sent)->count(),
        ];

        $charts = [
            'registrations' => $this->monthlyCounts(Patient::class, 'created_at'),
            'checkups' => $this->monthlyCounts(CheckupRecord::class, 'visit_date'),
            'immunizations' => [
                'labels' => ['Completed', 'Scheduled', 'Overdue'],
                'data' => [
                    ImmunizationRecord::query()->where('vaccination_date', '<=', now())->count(),
                    ImmunizationRecord::query()->where('next_schedule', '>=', now())->count(),
                    ImmunizationRecord::query()->where('next_schedule', '<', now())->count(),
                ],
            ],
            'maternal' => $this->monthlyCounts(MaternalCareRecord::class, 'visit_date'),
        ];

        $recentAnnouncements = Announcement::query()
            ->with('postedBy')
            ->where('status', AnnouncementStatus::Active)
            ->orderByDesc('publish_date')
            ->limit(3)
            ->get();

        return view('dashboard', compact('stats', 'charts', 'recentAnnouncements'));
    }

    /**
     * @param  class-string  $model
     * @return array{labels: list<string>, data: list<int>}
     */
    private function monthlyCounts(string $model, string $dateColumn): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $data[] = $model::query()
                ->whereYear($dateColumn, $month->year)
                ->whereMonth($dateColumn, $month->month)
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
