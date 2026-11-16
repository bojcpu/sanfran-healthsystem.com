<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreImmunizationRecordRequest;
use App\Models\ImmunizationRecord;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\ImmunizationNextScheduleScheduled;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ImmunizationRecordController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $immunizations = ImmunizationRecord::query()
            ->with(['patient', 'user'])
            ->when(! $request->filled('highlight') && $request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('vaccine_name', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($pq) use ($search) {
                            $pq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('patient_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! $request->filled('highlight') && $request->filled('date'), fn ($q) => $q->whereDate('vaccination_date', '=', $request->input('date')))
            ->when($request->filled('highlight'), fn ($query) => $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$request->integer('highlight')]))
            ->latest('vaccination_date')
            ->paginate(15)
            ->withQueryString();

        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->orderBy('last_name')->get();

        return view('immunizations.index', compact('immunizations', 'patients'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('immunizations.index');
    }

    public function store(StoreImmunizationRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $record = ImmunizationRecord::query()->create($data);

        $this->notifyMidwivesOfNextSchedule($record);

        $this->activityLog->log('create', "Recorded immunization: {$record->vaccine_name}.", 'Immunization Records');

        return redirect()->route('immunizations.index')->with('success', 'Immunization record saved successfully.');
    }

    public function edit(ImmunizationRecord $immunization): View
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->orderBy('last_name')->get();

        return view('immunizations.edit', compact('immunization', 'patients'));
    }

    public function update(StoreImmunizationRecordRequest $request, ImmunizationRecord $immunization): RedirectResponse
    {
        $data = $request->validated();
        $scheduleChanged = $immunization->next_schedule?->toDateString() !== ($data['next_schedule'] ?? null);

        if ($scheduleChanged) {
            $this->markPreviousNextScheduleNotificationsRead($immunization);
        }

        $immunization->update($data);

        if ($scheduleChanged) {
            $this->notifyMidwivesOfNextSchedule($immunization);
        }

        $this->activityLog->log('update', "Updated immunization record #{$immunization->id}.", 'Immunization Records');

        $redirect = $request->input('_return_to') === 'profile'
            ? redirect()->route('patients.show', $data['patient_id'])
            : redirect()->route('immunizations.index');

        return $redirect->with('success', 'Immunization record updated successfully.');
    }

    public function destroy(ImmunizationRecord $immunization): RedirectResponse
    {
        $user = Auth::user();

        if (! ($user instanceof User)) {
            abort(403);
        }

        if (! $user->role->canArchiveRecords()) {
            abort(403);
        }

        ImmunizationRecord::destroy($immunization->getKey());

        $this->activityLog->log('archive', "Archived immunization record #{$immunization->id}.", 'Immunization Records');

        return redirect()->route('immunizations.index')->with('success', 'Immunization record archived successfully.');
    }

    private function notifyMidwivesOfNextSchedule(ImmunizationRecord $record): void
    {
        if (! $record->next_schedule || ! $this->shouldNotifyMidwifeForNextScheduleReminder($record)) {
            return;
        }

        User::query()
            ->where('role', UserRole::Midwife)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $midwife) => $midwife->notify(new ImmunizationNextScheduleScheduled($record)));
    }

    private function shouldNotifyMidwifeForNextScheduleReminder(ImmunizationRecord $record): bool
    {
        $today = Carbon::today()->startOfDay();
        $nextSchedule = Carbon::parse($record->next_schedule)->startOfDay();
        $daysUntilSchedule = (int) $today->diffInDays($nextSchedule, false);

        return $daysUntilSchedule >= 0 && $daysUntilSchedule <= 3;
    }

    private function markPreviousNextScheduleNotificationsRead(ImmunizationRecord $record): void
    {
        User::query()
            ->where('role', UserRole::Midwife)
            ->get()
            ->each(function (User $midwife) use ($record): void {
                $midwife->notifications()
                    ->where('type', ImmunizationNextScheduleScheduled::class)
                    ->get()
                    ->each(function ($notification) use ($record): void {
                        if ((int) ($notification->data['record_id'] ?? 0) === $record->id) {
                            $notification->markAsRead();
                        }
                    });
            });
    }
}
