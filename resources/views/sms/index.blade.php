@extends('layouts.app')

@section('content')
    <x-page-header title="SMS Notifications" description="Send and track health-related SMS messages.">
        <x-slot:actions>
            <button type="button" class="btn-primary" @click="$store.modal.open('send-sms')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Send SMS
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6 p-4" x-data="{ debounceTimer: null }">
        <form method="GET" action="{{ route('sms.index') }}" id="smsFilterForm" class="flex flex-col gap-3 lg:flex-row">
            <x-record-search-input
                source="sms"
                :value="request('search')"
                placeholder="Search patient or message..."
                input-class="form-input flex-1"
                @input="clearTimeout(debounceTimer); debounceTimer = setTimeout(() => $el.form.submit(), 400)"
            />
            <input type="date" name="date" value="{{ request('date') }}" aria-label="Sent date" class="form-input lg:w-40" @change="$el.form.submit()">
            <select name="status" class="form-select lg:w-40" @change="$el.form.submit()">
                <option value="">All Status</option>
                @foreach (\App\Enums\SmsStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card overflow-hidden" x-data="{ selectedSms: { mobileNumber: '', message: '' } }">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Date</th>
                        <th class="table-header table-cell">Patient</th>
                        <th class="table-header table-cell">Mobile</th>
                        <th class="table-header table-cell">Type</th>
                        <th class="table-header table-cell">Message</th>
                        <th class="table-header table-cell">Status</th>
                        <th class="table-header table-cell">Sent By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($smsNotifications as $sms)
                        <tr>
                            <td class="table-cell">{{ $sms->sent_at?->format('M d, Y h:i A') ?? $sms->created_at->format('M d, Y') }}</td>
                            <td class="table-cell">
                                @if ($sms->patient)
                                    <a href="{{ route('patients.show', $sms->patient) }}" class="text-primary hover:underline">{{ $sms->patient->full_name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="table-cell font-mono text-xs">{{ $sms->mobile_number }}</td>
                            <td class="table-cell">{{ $sms->notification_type->label() }}</td>
                            <td class="table-cell max-w-xs">
                                <button
                                    type="button"
                                    class="block max-w-xs truncate text-left hover:text-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                    @click="selectedSms = { mobileNumber: @js($sms->mobile_number), message: @js($sms->message) }; $store.modal.open('sms-detail')"
                                    aria-label="View SMS message details"
                                >{{ $sms->message }}</button>
                            </td>
                            <td class="table-cell">
                                @if ($sms->status->value === 'sent')
                                    <span class="badge-success">{{ $sms->status->label() }}</span>
                                @elseif ($sms->status->value === 'failed')
                                    <span class="badge-danger">{{ $sms->status->label() }}</span>
                                @else
                                    <span class="badge-warning">{{ $sms->status->label() }}</span>
                                @endif
                            </td>
                            <td class="table-cell">{{ $sms->sentBy?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No SMS notifications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($smsNotifications->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $smsNotifications->links() }}</div>
        @endif

        <x-modal name="sms-detail" title="SMS Details" maxWidth="md">
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Mobile number</dt>
                    <dd class="mt-1 font-mono text-sm text-gray-900" x-text="selectedSms.mobileNumber"></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Message sent</dt>
                    <dd class="mt-1 whitespace-pre-wrap text-sm text-gray-900" x-text="selectedSms.message"></dd>
                </div>
            </dl>
        </x-modal>
    </div>

    <x-modal name="send-sms" title="Send SMS" description="Send health reminders and advisories via SMS." maxWidth="2xl">
        @include('sms.partials.form')
    </x-modal>
@endsection
