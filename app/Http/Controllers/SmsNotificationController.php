<?php

namespace App\Http\Controllers;

use App\Enums\SmsNotificationType;
use App\Enums\SmsStatus;
use App\Http\Requests\SendSmsRequest;
use App\Models\Patient;
use App\Models\SmsNotification;
use App\Services\ActivityLogService;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsNotificationController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private SmsService $smsService,
    ) {}

    public function index(Request $request): View
    {
        $smsNotifications = SmsNotification::query()
            ->with(['patient', 'sentBy'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mobile_number', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($pq) use ($search) {
                            $pq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->date, fn ($q, $date) => $q->whereDate('sent_at', '=', $date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $patients = Patient::query()
            ->whereNotNull('contact_number')
            ->orderBy('last_name')
            ->get();

        return view('sms.index', compact('smsNotifications', 'patients'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('sms.index');
    }

    public function store(SendSmsRequest $request): RedirectResponse
    {
        $notification = $this->smsService->send(
            $request->input('mobile_number'),
            $request->input('message'),
            SmsNotificationType::from($request->input('notification_type')),
            auth()->user(),
            $request->input('patient_id'),
        );

        $this->activityLog->log(
            'sms',
            "SMS {$notification->status->value} to {$notification->mobile_number}.",
            'SMS Notifications'
        );

        $redirect = $request->input('return_to')
            ? redirect()->route($request->input('return_to'))
            : redirect()->route('sms.index');

        if ($notification->status === SmsStatus::Sent) {
            return $redirect->with('success', 'SMS sent successfully.');
        }

        return $redirect->with('error', 'SMS sending failed. The attempt was saved in SMS history.');
    }
}
