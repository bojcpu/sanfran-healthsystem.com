@extends('layouts.app')

@section('content')
    @php
        $canArchive = auth()->user()->role->canArchiveRecords();
        $canManage = ! auth()->user()->isSystemAdmin();
    @endphp

    <x-page-header title="Patients" description="Register and manage patient records.">
        <x-slot:actions>
            @if ($canManage)
                <button type="button" class="btn-primary" @click="$store.modal.open('register-patient')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Register Patient
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('patients.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="patients" :value="request('search')" placeholder="Search by name, code, or contact..." />
            <select name="gender" class="form-select sm:w-40">
                <option value="">All Genders</option>
                <option value="Male" @selected(request('gender') === 'Male')>Male</option>
                <option value="Female" @selected(request('gender') === 'Female')>Female</option>
            </select>
            <button type="submit" class="btn-primary sm:self-end">Search</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Code</th>
                        <th class="table-header table-cell">Name</th>
                        <th class="table-header table-cell">Age</th>
                        <th class="table-header table-cell">Gender</th>
                        <th class="table-header table-cell">Contact</th>
                        <th class="table-header table-cell">Registered</th>
                        <th class="table-header table-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="table-cell font-mono text-xs text-primary">{{ $patient->patient_code }}</td>
                            <td class="table-cell font-medium text-gray-900">{{ $patient->full_name }}</td>
                            <td class="table-cell">{{ $patient->age ?? $patient->computeAge() }}</td>
                            <td class="table-cell">{{ $patient->gender }}</td>
                            <td class="table-cell">{{ $patient->contact_number ?? '—' }}</td>
                            <td class="table-cell">{{ $patient->created_at->format('M d, Y') }}</td>
                            <td class="table-cell">
                                <div class="table-actions">
                                    <a href="{{ route('patients.show', $patient) }}" class="btn-secondary text-xs">View</a>
                                    @if ($canManage)
                                        <button type="button" class="btn-secondary text-xs" @click="$store.modal.open('edit-patient-{{ $patient->id }}')">Edit</button>
                                    @endif
                                    @if ($canArchive && $canManage)
                                        <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" data-confirm-archive="Archive patient {{ $patient->full_name }}?" class="btn-primary text-xs">Archive</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No patients found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($patients->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $patients->links() }}</div>
        @endif
    </div>

    @if ($canManage)
        <x-modal name="register-patient" title="Register Patient" description="Add a new patient to the health system." maxWidth="5xl">
            @include('patients.partials.form')
        </x-modal>

        @foreach ($patients as $patient)
            @php $modalName = 'edit-patient-' . $patient->id; @endphp
            <x-modal :name="$modalName" title="Edit Patient" :description="$patient->full_name . ' (' . $patient->patient_code . ')'" maxWidth="5xl">
                @include('patients.partials.form', ['editingPatient' => $patient, 'modalName' => $modalName, 'returnTo' => 'index'])
            </x-modal>
        @endforeach
    @endif
@endsection
