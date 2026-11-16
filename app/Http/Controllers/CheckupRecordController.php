<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreCheckupRecordRequest;
use App\Models\CheckupRecord;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\CheckupFollowUpScheduled;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckupRecordController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $checkups = CheckupRecord::query()
            ->with(['patient', 'user'])
            ->when(! $request->filled('highlight') && $request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('diagnosis', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($patientQuery) use ($search) {
                            $patientQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('patient_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! $request->filled('highlight') && $request->filled('date'), fn ($q) => $q->whereDate('visit_date', '=', $request->input('date')))
            ->when($request->filled('highlight'), fn ($query) => $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$request->integer('highlight')]))
            ->latest('visit_date')
            ->paginate(15)
            ->withQueryString();

        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->orderBy('last_name')->get();

        return view('checkups.index', compact('checkups', 'patients'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('checkups.index');
    }

    public function store(StoreCheckupRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['attending_personnel'] = $data['attending_personnel'] ?? Auth::user()->name;

        $checkup = CheckupRecord::query()->create($data);

        $this->notifyMidwivesOfFollowUp($checkup);

        $this->activityLog->log('create', "Created checkup record for patient ID {$checkup->patient_id}.", 'Checkup Records');

        return redirect()->route('checkups.index')->with('success', 'Checkup record saved successfully.');
    }

    public function edit(CheckupRecord $checkup): View
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->orderBy('last_name')->get();

        return view('checkups.edit', compact('checkup', 'patients'));
    }

    public function update(StoreCheckupRecordRequest $request, CheckupRecord $checkup): RedirectResponse
    {
        $data = $request->validated();
        $followUpChanged = $checkup->follow_up_date?->toDateString() !== ($data['follow_up_date'] ?? null);

        if ($followUpChanged) {
            $this->markPreviousFollowUpNotificationsRead($checkup);
        }

        $checkup->update($data);

        if ($followUpChanged) {
            $this->notifyMidwivesOfFollowUp($checkup);
        }

        $this->activityLog->log('update', "Updated checkup record #{$checkup->id}.", 'Checkup Records');

        $redirect = $request->input('_return_to') === 'profile'
            ? redirect()->route('patients.show', $data['patient_id'])
            : redirect()->route('checkups.index');

        return $redirect->with('success', 'Checkup record updated successfully.');
    }

    public function destroy(CheckupRecord $checkup): RedirectResponse
    {
        $user = Auth::user();

        if (! ($user instanceof User)) {
            abort(403);
        }

        if (! $user->role->canArchiveRecords()) {
            abort(403);
        }

        CheckupRecord::destroy($checkup->getKey());

        $this->activityLog->log('archive', "Archived checkup record #{$checkup->id}.", 'Checkup Records');

        return redirect()->route('checkups.index')->with('success', 'Checkup record archived successfully.');
    }

    private function notifyMidwivesOfFollowUp(CheckupRecord $record): void
    {
        if (! $record->follow_up_date || ! $this->shouldNotifyMidwifeForFollowUpReminder($record)) {
            return;
        }

        User::query()
            ->where('role', UserRole::Midwife)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $midwife) => $midwife->notify(new CheckupFollowUpScheduled($record)));
    }

    private function shouldNotifyMidwifeForFollowUpReminder(CheckupRecord $record): bool
    {
        $today = Carbon::today()->startOfDay();
        $followUpDate = Carbon::parse($record->follow_up_date)->startOfDay();
        $daysUntilFollowUp = (int) $today->diffInDays($followUpDate, false);

        return $daysUntilFollowUp >= 0 && $daysUntilFollowUp <= 3;
    }

    private function markPreviousFollowUpNotificationsRead(CheckupRecord $record): void
    {
        User::query()
            ->where('role', UserRole::Midwife)
            ->get()
            ->each(function (User $midwife) use ($record): void {
                $midwife->notifications()
                    ->where('type', CheckupFollowUpScheduled::class)
                    ->get()
                    ->each(function ($notification) use ($record): void {
                        if ((int) ($notification->data['record_id'] ?? 0) === $record->id) {
                            $notification->markAsRead();
                        }
                    });
            });
    }
}
