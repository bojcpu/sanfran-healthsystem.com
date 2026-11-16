<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Enums\UserRole;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PatientCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private PatientCodeService $patientCodeService,
    ) {}

    public function index(Request $request): View
    {
        $patients = Patient::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->when($request->gender, fn ($q, $gender) => $q->where('gender', $gender))
            ->when($request->civil_status, fn ($q, $status) => $q->where('civil_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('patients.index');
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['purok']);
        $data['patient_code'] = $this->patientCodeService->generate();
        $data['age'] = (int) \Carbon\Carbon::parse($data['birth_date'])->age;
        $data['registered_by'] = Auth::id();

        $patient = Patient::query()->create($data);

        $this->activityLog->log('create', "Registered patient {$patient->patient_code}: {$patient->full_name}.", 'Patient Management');

        return redirect()->route('patients.index')->with('success', 'Patient registered successfully.');
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'checkupRecords.user',
            'immunizationRecords.user',
            'maternalCareRecords.user',
        ]);
        $patients = Patient::query()->orderBy('last_name')->get();

        return view('patients.show', compact('patient', 'patients'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $data = $request->validated();
        unset($data['purok']);
        $data['age'] = (int) \Carbon\Carbon::parse($data['birth_date'])->age;

        $patient->update($data);

        $this->activityLog->log('update', "Updated patient {$patient->patient_code}: {$patient->full_name}.", 'Patient Management');

        $redirect = $request->input('_return_to') === 'index'
            ? redirect()->route('patients.index')
            : redirect()->route('patients.show', $patient);

        return $redirect->with('success', 'Patient record updated successfully.');
    }

    public function destroy(Patient $patient): RedirectResponse
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

        $code = $patient->patient_code;
        $name = $patient->full_name;
        Patient::destroy($patient->getKey());

        $this->activityLog->log('archive', "Archived patient {$code}: {$name}.", 'Patient Management');

        return redirect()->route('patients.index')->with('success', 'Patient record archived successfully.');
    }
}
