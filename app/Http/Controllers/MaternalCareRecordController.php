<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreMaternalCareRecordRequest;
use App\Notifications\MaternalCareFollowUpScheduled;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MaternalCareRecordController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $records = MaternalCareRecord::query()
            ->with(['patient', 'user'])
            ->when(! $request->filled('highlight') && $request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                });
            })
            ->when(! $request->filled('highlight') && $request->filled('date'), fn ($q) => $q->whereDate('visit_date', '=', $request->input('date')))
            ->when($request->filled('highlight'), fn ($query) => $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$request->integer('highlight')]))
            ->latest('visit_date')
            ->paginate(15)
            ->withQueryString();

        $patients = Patient::query()->where('gender', 'Female')->orderBy('last_name')->get();

        return view('maternal-care.index', compact('records', 'patients'));
    }

    public function create(): View
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->where('gender', 'Female')->orderBy('last_name')->get();

        return view('maternal-care.create', compact('patients'));
    }

    public function store(StoreMaternalCareRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['expected_delivery_date'] = blank($data['expected_delivery_date'] ?? null) && filled($data['last_menstrual_period'] ?? null)
            ? Carbon::parse($data['last_menstrual_period'])->addDays(280)->toDateString()
            : ($data['expected_delivery_date'] ?? null);

        $record = MaternalCareRecord::query()->create($data);

        $this->notifyMidwivesOfFollowUp($record);

        $this->activityLog->log('create', "Created maternal care record for patient ID {$record->patient_id}.", 'Maternal Care Records');

        return redirect()->route('maternal-care.index')->with('success', 'Maternal care record saved successfully.');
    }

    public function edit(MaternalCareRecord $maternal_care): View
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Patient> $patientQuery */
        $patientQuery = Patient::query();
        $patients = $patientQuery->where('gender', 'Female')->orderBy('last_name')->get();

        return view('maternal-care.edit', ['record' => $maternal_care, 'patients' => $patients]);
    }

    public function update(StoreMaternalCareRecordRequest $request, MaternalCareRecord $maternal_care): RedirectResponse
    {
        $data = $request->validated();
        $data['expected_delivery_date'] = blank($data['expected_delivery_date'] ?? null) && filled($data['last_menstrual_period'] ?? null)
            ? Carbon::parse($data['last_menstrual_period'])->addDays(280)->toDateString()
            : ($data['expected_delivery_date'] ?? null);

        $followUpChanged = $maternal_care->follow_up_date?->toDateString() !== ($data['follow_up_date'] ?? null);

        if ($followUpChanged) {
            $this->markPreviousFollowUpNotificationsRead($maternal_care);
        }

        $maternal_care->update($data);

        if ($followUpChanged) {
            $this->notifyMidwivesOfFollowUp($maternal_care);
        }

        $this->activityLog->log('update', "Updated maternal care record #{$maternal_care->id}.", 'Maternal Care Records');

        $redirect = $request->input('_return_to') === 'profile'
            ? redirect()->route('patients.show', $data['patient_id'])
            : redirect()->route('maternal-care.index');

        return $redirect->with('success', 'Maternal care record updated successfully.');
    }

    public function destroy(MaternalCareRecord $maternal_care): RedirectResponse
    {
        $user = Auth::user();

        if (! ($user instanceof User)) {
            abort(403);
        }

        /** @var UserRole $role */
        $role = $user->role;

        if (! $role->canArchiveRecords()) {
            abort(403);
        }

        MaternalCareRecord::destroy($maternal_care->getKey());

        $this->activityLog->log('archive', "Archived maternal care record #{$maternal_care->id}.", 'Maternal Care Records');

        return redirect()->route('maternal-care.index')->with('success', 'Maternal care record archived successfully.');
    }

    private function notifyMidwivesOfFollowUp(MaternalCareRecord $record): void
    {
        if (! $record->follow_up_date || ! $this->shouldNotifyMidwifeForFollowUpReminder($record)) {
            return;
        }

        User::query()
            ->where('role', UserRole::Midwife)
            ->where('is_active', true)
            ->get()
            ->each(fn (User $midwife) => $midwife->notify(new MaternalCareFollowUpScheduled($record)));
    }

    private function shouldNotifyMidwifeForFollowUpReminder(MaternalCareRecord $record): bool
    {
        $today = Carbon::today()->startOfDay();
        $followUpDate = Carbon::parse($record->follow_up_date)->startOfDay();
        $daysUntilFollowUp = (int) $today->diffInDays($followUpDate, false);

        return $daysUntilFollowUp >= 0 && $daysUntilFollowUp <= 3;
    }

    private function markPreviousFollowUpNotificationsRead(MaternalCareRecord $record): void
    {
        User::query()
            ->where('role', UserRole::Midwife)
            ->get()
            ->each(function (User $midwife) use ($record): void {
                $midwife->notifications()
                    ->where('type', MaternalCareFollowUpScheduled::class)
                    ->get()
                    ->each(function ($notification) use ($record): void {
                        if ((int) ($notification->data['record_id'] ?? 0) === $record->id) {
                            $notification->markAsRead();
                        }
                    });
            });
    }
}
