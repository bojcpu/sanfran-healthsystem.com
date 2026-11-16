@extends('layouts.app')

@section('content')
    @php $canSendSms = auth()->user()->isSystemAdmin() || auth()->user()->isMidwife(); @endphp
    <x-page-header title="Maternal Care" description="Prenatal and maternal health records.">
        <x-slot:actions>
            @if (auth()->user()->isMidwife())
                <a href="{{ route('maternal-care.create') }}" class="btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Record
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('maternal-care.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="maternal-care" :value="request('search')" placeholder="Search patient..." />
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
                        <th class="table-header table-cell">Trimester Period</th>
                        <th class="table-header table-cell">Visit Date</th>
                        <th class="table-header table-cell">Follow-up</th>
                        <th class="table-header table-cell">BP</th>
                        <th class="table-header table-cell">Weight</th>
                        <th class="table-header table-cell">EDD</th>
                        @if (auth()->user()->isMidwife() || $canSendSms)
                            <th class="table-header table-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($records as $record)
                        <tr
                            id="maternal-care-record-{{ $record->id }}"
                            @class(['notification-highlight' => (string) request('highlight') === (string) $record->id])
                        >
                            <td class="table-cell">
                                @if ($record->patient)
                                    <a href="{{ route('patients.show', $record->patient) }}" class="font-medium text-primary hover:underline">{{ $record->patient->full_name }}</a>
                                @else
                                    <span class="text-gray-500">Patient record unavailable</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $record->trimester_period ?? '—' }}</td>
                            <td class="table-cell">{{ $record->visit_date->format('M d, Y') }}</td>
                            <td class="table-cell">{{ $record->follow_up_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="table-cell">{{ $record->blood_pressure ?? '—' }}</td>
                            <td class="table-cell">{{ $record->weight ?? '—' }}</td>
                            <td class="table-cell">{{ $record->expected_delivery_date?->format('M d, Y') ?? '—' }}</td>
                            @if (auth()->user()->isMidwife() || $canSendSms)
                                <td class="table-cell">
                                    <div class="table-actions">
                                        @if (auth()->user()->isMidwife())
                                            <button type="button" class="btn-secondary text-xs" @click="$store.modal.open('edit-maternal-care-{{ $record->id }}')">Edit</button>
                                        @endif
                                        @if ($canSendSms && $record->patient)
                                            @php
                                                $patientName = $record->patient->full_name;
                                                $message = $record->follow_up_date
                                                    ? "Reminder: Dear {$patientName}, your maternal care appointment is on {$record->follow_up_date->format('M d, Y')}. - SanFran HealthSys"
                                                    : "Hello {$patientName}, please contact SanFran HealthSys to arrange your maternal care follow-up.";
                                            @endphp
                                            <button
                                                type="button"
                                                class="btn-primary text-xs"
                                                @click="$dispatch('open-record-sms', @js([
                                                    'patientName' => $patientName,
                                                    'patientId' => $record->patient->id,
                                                    'mobileNumber' => $record->patient->contact_number ?? '',
                                                    'notificationType' => \App\Enums\SmsNotificationType::MaternalCareReminder->value,
                                                    'message' => $message,
                                                ]))"
                                            >Send</button>
                                        @endif
                                        @if (auth()->user()->isMidwife())
                                            <form method="POST" action="{{ route('maternal-care.destroy', $record) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" data-confirm-archive="Archive this maternal care record?" class="btn-primary text-xs">Archive</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No maternal care records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $records->links() }}</div>
        @endif
    </div>

    @if ($canSendSms)
        @include('sms.partials.record-message-modal', ['returnTo' => 'maternal-care.index'])
    @endif

    @if (auth()->user()->isMidwife())
        @foreach ($records as $record)
            @php $modalName = 'edit-maternal-care-' . $record->id; @endphp
            <x-modal :name="$modalName" title="Edit Maternal Care Record" :description="$record->patient?->full_name ?? 'Maternal care record'" maxWidth="5xl">
                @include('maternal-care.partials.form', ['record' => $record, 'patients' => $patients, 'modalName' => $modalName, 'returnTo' => 'index'])
            </x-modal>
        @endforeach
    @endif

@endsection
