@extends('layouts.app')

@section('content')
    @php $canSendSms = auth()->user()->isSystemAdmin() || auth()->user()->isMidwife(); @endphp
    <x-page-header title="Immunizations" description="Vaccination and immunization records.">
        <x-slot:actions>
            @unless (auth()->user()->isSystemAdmin())
                <button type="button" class="btn-primary" @click="$store.modal.open('new-immunization')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Immunization
                </button>
            @endunless
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('immunizations.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="immunizations" :value="request('search')" placeholder="Search patient or vaccine..." />
            <input type="date" name="date" value="{{ request('date') }}" aria-label="Vaccination date" class="form-input sm:w-40">
            <button type="submit" class="btn-primary sm:self-end">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Patient</th>
                        <th class="table-header table-cell">Vaccine</th>
                        <th class="table-header table-cell">Dose</th>
                        <th class="table-header table-cell">Date</th>
                        <th class="table-header table-cell">Next Schedule</th>
                        @if (! auth()->user()->isSystemAdmin() || $canSendSms)
                            <th class="table-header table-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($immunizations as $record)
                        <tr
                            id="immunization-record-{{ $record->id }}"
                            @class(['notification-highlight' => (string) request('highlight') === (string) $record->id])
                        >
                            <td class="table-cell">
                                @if ($record->patient)
                                    <a href="{{ route('patients.show', $record->patient) }}" class="font-medium text-primary hover:underline">{{ $record->patient->full_name }}</a>
                                @else
                                    <span class="text-gray-500">Patient record unavailable</span>
                                @endif
                            </td>
                            <td class="table-cell font-medium">{{ $record->vaccine_name }}</td>
                            <td class="table-cell">{{ $record->dose ?? '—' }}</td>
                            <td class="table-cell">{{ $record->vaccination_date->format('M d, Y') }}</td>
                            <td class="table-cell">
                                @if ($record->next_schedule)
                                    <span @class(['badge-warning' => $record->next_schedule->isPast(), 'badge-info' => ! $record->next_schedule->isPast()])>
                                        {{ $record->next_schedule->format('M d, Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            @if (! auth()->user()->isSystemAdmin() || $canSendSms)
                                <td class="table-cell">
                                    <div class="table-actions">
                                        @unless (auth()->user()->isSystemAdmin())
                                            <button type="button" class="btn-secondary text-xs" @click="$store.modal.open('edit-immunization-{{ $record->id }}')">Edit</button>
                                        @endunless
                                        @if ($canSendSms && $record->patient)
                                            @php
                                                $patientName = $record->patient->full_name;
                                                $message = $record->next_schedule
                                                    ? "Reminder: Dear {$patientName}, your child's immunization schedule is on {$record->next_schedule->format('M d, Y')}. - SanFran HealthSys"
                                                    : "Hello {$patientName}, please contact SanFran HealthSys to arrange your next immunization schedule.";
                                            @endphp
                                            <button
                                                type="button"
                                                class="btn-primary text-xs"
                                                @click="$dispatch('open-record-sms', @js([
                                                    'patientName' => $patientName,
                                                    'patientId' => $record->patient->id,
                                                    'mobileNumber' => $record->patient->contact_number ?? '',
                                                    'notificationType' => \App\Enums\SmsNotificationType::ImmunizationReminder->value,
                                                    'message' => $message,
                                                ]))"
                                            >Send</button>
                                        @endif
                                        @unless (auth()->user()->isSystemAdmin())
                                            @if (auth()->user()->role->canArchiveRecords())
                                                <form method="POST" action="{{ route('immunizations.destroy', $record) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" data-confirm-archive="Archive this immunization record?" class="btn-primary text-xs">Archive</button>
                                                </form>
                                            @endif
                                        @endunless
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No immunization records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($immunizations->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $immunizations->links() }}</div>
        @endif
    </div>

    @if ($canSendSms)
        @include('sms.partials.record-message-modal', ['returnTo' => 'immunizations.index'])
    @endif

    @unless (auth()->user()->isSystemAdmin())
        <x-modal name="new-immunization" title="New Immunization" description="Record a vaccination." maxWidth="2xl">
            @include('immunizations.partials.form')
        </x-modal>

        @foreach ($immunizations as $record)
            @php $modalName = 'edit-immunization-' . $record->id; @endphp
            <x-modal :name="$modalName" title="Edit Immunization" :description="$record->patient?->full_name ?? 'Immunization record'" maxWidth="2xl">
                @include('immunizations.partials.form', ['editingImmunization' => $record, 'modalName' => $modalName, 'returnTo' => 'index'])
            </x-modal>
        @endforeach
    @endunless
@endsection
