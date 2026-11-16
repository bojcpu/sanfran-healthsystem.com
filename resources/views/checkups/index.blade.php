@extends('layouts.app')

@section('content')
    @php $canSendSms = auth()->user()->isSystemAdmin() || auth()->user()->isMidwife(); @endphp
    <x-page-header title="Checkups" description="Patient consultation and checkup records.">
        <x-slot:actions>
            @unless (auth()->user()->isSystemAdmin())
                <button type="button" class="btn-primary" @click="$store.modal.open('new-checkup')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Checkup
                </button>
            @endunless
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('checkups.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="checkups" :value="request('search')" placeholder="Search patient or diagnosis..." input-class="form-input h-12 flex-1" />
            <input type="date" name="date" value="{{ request('date') }}" aria-label="Visit date" class="form-input sm:w-40">
            <button type="submit" class="btn-primary sm:self-end">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Patient</th>
                        <th class="table-header table-cell">Visit Date</th>
                        <th class="table-header table-cell">Complaint</th>
                        <th class="table-header table-cell">Diagnosis</th>
                        <th class="table-header table-cell">Follow-up</th>
                        <th class="table-header table-cell">Recorded By</th>
                        @if (! auth()->user()->isSystemAdmin() || $canSendSms)
                            <th class="table-header table-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($checkups as $checkup)
                        <tr
                            id="checkup-record-{{ $checkup->id }}"
                            @class(['notification-highlight' => (string) request('highlight') === (string) $checkup->id])
                        >
                            <td class="table-cell">
                                @if ($checkup->patient)
                                    <a href="{{ route('patients.show', $checkup->patient) }}" class="font-medium text-primary hover:underline">{{ $checkup->patient->full_name }}</a>
                                    <p class="text-xs text-gray-500">{{ $checkup->patient->patient_code }}</p>
                                @else
                                    <span class="text-gray-500">Patient record unavailable</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $checkup->visit_date->format('M d, Y') }}</td>
                            <td class="table-cell">{{ Str::limit($checkup->complaint, 35) ?? '—' }}</td>
                            <td class="table-cell">{{ Str::limit($checkup->diagnosis, 35) ?? '—' }}</td>
                            <td class="table-cell">{{ $checkup->follow_up_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="table-cell">{{ $checkup->user?->name ?? '—' }}</td>
                            @if (! auth()->user()->isSystemAdmin() || $canSendSms)
                                <td class="table-cell">
                                    <div class="table-actions">
                                        @unless (auth()->user()->isSystemAdmin())
                                            <button type="button" class="btn-secondary text-xs" @click="$store.modal.open('edit-checkup-{{ $checkup->id }}')">Edit</button>
                                        @endunless
                                        @if ($canSendSms && $checkup->patient)
                                            @php
                                                $patientName = $checkup->patient->full_name;
                                                $message = $checkup->follow_up_date
                                                    ? "Reminder: Dear {$patientName}, your scheduled checkup is on {$checkup->follow_up_date->format('M d, Y')}. - SanFran HealthSys"
                                                    : "Hello {$patientName}, please contact SanFran HealthSys to arrange your checkup follow-up.";
                                            @endphp
                                            <button
                                                type="button"
                                                class="btn-primary text-xs"
                                                @click="$dispatch('open-record-sms', @js([
                                                    'patientName' => $patientName,
                                                    'patientId' => $checkup->patient->id,
                                                    'mobileNumber' => $checkup->patient->contact_number ?? '',
                                                    'notificationType' => \App\Enums\SmsNotificationType::CheckupReminder->value,
                                                    'message' => $message,
                                                ]))"
                                            >Send</button>
                                        @endif
                                        @unless (auth()->user()->isSystemAdmin())
                                            @if (auth()->user()->role->canArchiveRecords())
                                                <form method="POST" action="{{ route('checkups.destroy', $checkup) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" data-confirm-archive="Archive this checkup record?" class="btn-primary text-xs">Archive</button>
                                                </form>
                                            @endif
                                        @endunless
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No checkup records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($checkups->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $checkups->links() }}</div>
        @endif
    </div>

    @if ($canSendSms)
        @include('sms.partials.record-message-modal', ['returnTo' => 'checkups.index'])
    @endif

    @unless (auth()->user()->isSystemAdmin())
        <x-modal name="new-checkup" title="New Checkup" description="Record a patient consultation." maxWidth="3xl">
            @include('checkups.partials.form')
        </x-modal>

        @foreach ($checkups as $checkup)
            @php $modalName = 'edit-checkup-' . $checkup->id; @endphp
            <x-modal :name="$modalName" title="Edit Checkup" :description="$checkup->patient?->full_name ?? 'Checkup record'" maxWidth="3xl">
                @include('checkups.partials.form', ['editingCheckup' => $checkup, 'modalName' => $modalName, 'returnTo' => 'index'])
            </x-modal>
        @endforeach
    @endunless
@endsection
