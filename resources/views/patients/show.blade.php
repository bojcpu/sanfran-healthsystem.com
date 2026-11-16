@extends('layouts.app')

@section('content')
    <div x-data="{ activeTab: '{{ request('tab', 'personal') }}' }">
        <x-page-header :title="$patient->full_name" :description="'Patient Code: ' . $patient->patient_code">
            <x-slot:actions>
                @unless (auth()->user()->isSystemAdmin())
                    <button type="button" class="btn-secondary" @click="$store.modal.open('edit-patient-{{ $patient->id }}')">Edit</button>
                @endunless
                <a href="{{ route('patients.index') }}" class="btn-secondary">Back</a>
            </x-slot:actions>
        </x-page-header>

        {{-- Patient summary card --}}
        <div class="card mb-6 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-white">
                        {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $patient->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $patient->gender }} · {{ $patient->age ?? $patient->computeAge() }} years old · {{ $patient->blood_type ?? 'Blood type N/A' }}</p>
                        <p class="text-sm text-gray-500">{{ $patient->contact_number ?? 'No contact number.' }}</p>
                    </div>
                </div>
                @unless (auth()->user()->isSystemAdmin())
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('checkups.create', ['patient_id' => $patient->id]) }}" class="btn-secondary text-xs">New Checkup</a>
                        <a href="{{ route('immunizations.create', ['patient_id' => $patient->id]) }}" class="btn-secondary text-xs">New Immunization</a>
                        @if ($patient->gender === 'Female' && ! auth()->user()->isBhw())
                            <a href="{{ route('maternal-care.create', ['patient_id' => $patient->id]) }}" class="btn-secondary text-xs">Maternal Care</a>
                        @endif
                    </div>
                @endunless
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex gap-4 overflow-x-auto">
                @foreach (['personal' => 'Personal Info', 'checkups' => 'Checkups', 'immunizations' => 'Immunizations', 'maternal' => 'Maternal Care'] as $tab => $label)
                    <button
                        type="button"
                        @click="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'tab-link-active' : 'tab-link'"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Personal Tab --}}
        <div x-show="activeTab === 'personal'" class="grid gap-6 lg:grid-cols-2">
            <div class="card p-5">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Personal Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Birth Date</dt><dd class="font-medium text-gray-900">{{ $patient->birth_date->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Civil Status</dt><dd class="font-medium text-gray-900">{{ $patient->civil_status }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $patient->email ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd class="max-w-xs text-right font-medium text-gray-900">{{ $patient->address }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Registered By</dt><dd class="font-medium text-gray-900">{{ $patient->registeredBy?->name ?? '—' }}</dd></div>
                </dl>
            </div>
            <div class="card p-5">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Emergency & Medical</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Emergency Contact</dt><dd class="font-medium text-gray-900">{{ $patient->emergency_contact_person ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Relationship</dt><dd class="font-medium text-gray-900">{{ $patient->emergency_contact_relationship ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Emergency Number</dt><dd class="font-medium text-gray-900">{{ $patient->emergency_contact_number ?? '—' }}</dd></div>
                    <div><dt class="mb-1 text-gray-500">Allergies</dt><dd class="font-medium text-gray-900">{{ $patient->allergies ?? 'None recorded.' }}</dd></div>
                    <div><dt class="mb-1 text-gray-500">Existing Conditions</dt><dd class="font-medium text-gray-900">{{ $patient->existing_conditions ?? 'None recorded.' }}</dd></div>
                    @if ($patient->notes)
                        <div><dt class="mb-1 text-gray-500">Notes</dt><dd class="font-medium text-gray-900">{{ $patient->notes }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Checkups Tab --}}
        <div x-show="activeTab === 'checkups'" x-cloak class="grid gap-4 lg:grid-cols-2">
            @forelse ($patient->checkupRecords as $checkup)
                <article class="card p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">Checkup Record</h3>
                        @unless (auth()->user()->isSystemAdmin())
                            <button type="button" class="text-sm text-primary hover:underline" @click="$store.modal.open('edit-checkup-{{ $checkup->id }}')">Edit</button>
                        @endunless
                    </div>
                    <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        <div><dt class="mb-1 text-gray-500">Visit Date</dt><dd class="font-medium text-gray-900">{{ $checkup->visit_date->format('M d, Y') }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Follow-up Date</dt><dd class="font-medium text-gray-900">{{ $checkup->follow_up_date?->format('M d, Y') ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Attending Personnel</dt><dd class="font-medium text-gray-900">{{ $checkup->attending_personnel ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Recorded By</dt><dd class="font-medium text-gray-900">{{ $checkup->user?->name ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Chief Complaint</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $checkup->complaint ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Diagnosis</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $checkup->diagnosis ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Treatment</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $checkup->treatment ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Prescription</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $checkup->prescription ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="mb-1 text-gray-500">Notes</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $checkup->notes ?? '—' }}</dd></div>
                    </dl>
                </article>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 lg:col-span-2">No checkup records.</p>
            @endforelse
        </div>

        {{-- Immunizations Tab --}}
        <div x-show="activeTab === 'immunizations'" x-cloak class="grid gap-4 lg:grid-cols-2">
            @forelse ($patient->immunizationRecords as $record)
                <article class="card p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">Immunization Record</h3>
                        @unless (auth()->user()->isSystemAdmin())
                            <button type="button" class="text-sm text-primary hover:underline" @click="$store.modal.open('edit-immunization-{{ $record->id }}')">Edit</button>
                        @endunless
                    </div>
                    <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        <div><dt class="mb-1 text-gray-500">Vaccine Name</dt><dd class="font-medium text-gray-900">{{ $record->vaccine_name }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Dose</dt><dd class="font-medium text-gray-900">{{ $record->dose ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Vaccination Date</dt><dd class="font-medium text-gray-900">{{ $record->vaccination_date->format('M d, Y') }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Next Schedule</dt><dd class="font-medium text-gray-900">{{ $record->next_schedule?->format('M d, Y') ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Recorded By</dt><dd class="font-medium text-gray-900">{{ $record->user?->name ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="mb-1 text-gray-500">Remarks</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $record->remarks ?? '—' }}</dd></div>
                    </dl>
                </article>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 lg:col-span-2">No immunization records.</p>
            @endforelse
        </div>

        {{-- Maternal Tab --}}
        <div x-show="activeTab === 'maternal'" x-cloak class="grid gap-4 lg:grid-cols-2">
            @forelse ($patient->maternalCareRecords as $record)
                <article class="card p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-gray-900">Maternal Care Record</h3>
                        @if (auth()->user()->isMidwife())
                            <button type="button" class="text-sm text-primary hover:underline" @click="$store.modal.open('edit-maternal-care-{{ $record->id }}')">Edit</button>
                        @endif
                    </div>
                    <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        <div><dt class="mb-1 text-gray-500">Gravida</dt><dd class="font-medium text-gray-900">{{ $record->pregnancy_number ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Para</dt><dd class="font-medium text-gray-900">{{ $record->para ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Current Trimester</dt><dd class="font-medium text-gray-900">{{ $record->trimester_period ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">AOG</dt><dd class="font-medium text-gray-900">{{ $record->gestational_weeks !== null ? $record->gestational_weeks . ' weeks ' . ($record->gestational_days ?? 0) . ' days' : '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Visit Date</dt><dd class="font-medium text-gray-900">{{ $record->visit_date->format('M d, Y') }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Expected Delivery Date</dt><dd class="font-medium text-gray-900">{{ $record->expected_delivery_date?->format('M d, Y') ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Prenatal Follow-up Date</dt><dd class="font-medium text-gray-900">{{ $record->follow_up_date?->format('M d, Y') ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Weight</dt><dd class="font-medium text-gray-900">{{ $record->weight ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Blood Pressure</dt><dd class="font-medium text-gray-900">{{ $record->blood_pressure ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Recorded By</dt><dd class="font-medium text-gray-900">{{ $record->user?->name ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Findings</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $record->findings ?? '—' }}</dd></div>
                        <div><dt class="mb-1 text-gray-500">Recommendations</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $record->recommendations ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="mb-1 text-gray-500">Remarks</dt><dd class="whitespace-pre-line break-words font-medium text-gray-900">{{ $record->remarks ?? '—' }}</dd></div>
                    </dl>
                </article>
            @empty
                <p class="py-8 text-center text-sm text-gray-500 lg:col-span-2">No maternal care records.</p>
            @endforelse
        </div>

    </div>

    @unless (auth()->user()->isSystemAdmin())
        @php $patientModalName = 'edit-patient-' . $patient->id; @endphp
        <x-modal :name="$patientModalName" title="Edit Patient" :description="$patient->full_name . ' (' . $patient->patient_code . ')'" maxWidth="5xl">
            @include('patients.partials.form', ['editingPatient' => $patient, 'modalName' => $patientModalName, 'returnTo' => 'profile'])
        </x-modal>

        @foreach ($patient->checkupRecords as $checkup)
            @php $modalName = 'edit-checkup-' . $checkup->id; @endphp
            <x-modal :name="$modalName" title="Edit Checkup" :description="$patient->full_name" maxWidth="3xl">
                @include('checkups.partials.form', ['editingCheckup' => $checkup, 'modalName' => $modalName, 'returnTo' => 'profile'])
            </x-modal>
        @endforeach

        @foreach ($patient->immunizationRecords as $record)
            @php $modalName = 'edit-immunization-' . $record->id; @endphp
            <x-modal :name="$modalName" title="Edit Immunization" :description="$patient->full_name" maxWidth="2xl">
                @include('immunizations.partials.form', ['editingImmunization' => $record, 'modalName' => $modalName, 'returnTo' => 'profile'])
            </x-modal>
        @endforeach
    @endunless

    @if (auth()->user()->isMidwife())
        @foreach ($patient->maternalCareRecords as $record)
            @php $modalName = 'edit-maternal-care-' . $record->id; @endphp
            <x-modal :name="$modalName" title="Edit Maternal Care Record" :description="$patient->full_name" maxWidth="5xl">
                @include('maternal-care.partials.form', ['record' => $record, 'patients' => $patients->where('gender', 'Female'), 'modalName' => $modalName, 'returnTo' => 'profile'])
            </x-modal>
        @endforeach
    @endif
@endsection
