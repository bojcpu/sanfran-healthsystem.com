@extends('layouts.app')

@php
    use App\Enums\ArchiveRecordType;
@endphp

@section('content')
    <x-page-header title="Archive" description="Hidden records stay saved here until you restore them." />

    <div class="mb-6 flex gap-6 overflow-x-auto border-b border-gray-200">
        @foreach ($types as $archiveType)
            <a
                href="{{ route('archive.index', ['type' => $archiveType->value]) }}"
                @class([$type === $archiveType ? 'tab-link-active' : 'tab-link'])
            >
                {{ $archiveType->label() }}
            </a>
        @endforeach
    </div>

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('archive.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="hidden" name="type" value="{{ $type->value }}">
            <x-record-search-input
                source="archive"
                :context="$type->value"
                :value="$search"
                placeholder="Search archived records..."
            />
            <button type="submit" class="btn-primary sm:self-end">Search</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    @if ($type === ArchiveRecordType::Patients)
                        <tr>
                            <th class="table-header table-cell">Code</th>
                            <th class="table-header table-cell">Name</th>
                            <th class="table-header table-cell">Age</th>
                            <th class="table-header table-cell">Gender</th>
                            <th class="table-header table-cell">Archived</th>
                            <th class="table-header table-cell">Actions</th>
                        </tr>
                    @elseif ($type === ArchiveRecordType::Checkups)
                        <tr>
                            <th class="table-header table-cell">Patient</th>
                            <th class="table-header table-cell">Visit Date</th>
                            <th class="table-header table-cell">Diagnosis</th>
                            <th class="table-header table-cell">Archived</th>
                            <th class="table-header table-cell">Actions</th>
                        </tr>
                    @elseif ($type === ArchiveRecordType::Immunizations)
                        <tr>
                            <th class="table-header table-cell">Patient</th>
                            <th class="table-header table-cell">Vaccine</th>
                            <th class="table-header table-cell">Date</th>
                            <th class="table-header table-cell">Archived</th>
                            <th class="table-header table-cell">Actions</th>
                        </tr>
                    @elseif ($type === ArchiveRecordType::MaternalCare)
                        <tr>
                            <th class="table-header table-cell">Patient</th>
                            <th class="table-header table-cell">Visit Date</th>
                            <th class="table-header table-cell">Pregnancy #</th>
                            <th class="table-header table-cell">Archived</th>
                            <th class="table-header table-cell">Actions</th>
                        </tr>
                    @else
                        <tr>
                            <th class="table-header table-cell">Title</th>
                            <th class="table-header table-cell">Type</th>
                            <th class="table-header table-cell">Archived</th>
                            <th class="table-header table-cell">Actions</th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($records as $record)
                        <tr>
                            @if ($type === ArchiveRecordType::Patients)
                                <td class="table-cell font-mono text-xs text-primary">{{ $record->patient_code }}</td>
                                <td class="table-cell font-medium text-gray-900">{{ $record->full_name }}</td>
                                <td class="table-cell">{{ $record->age ?? $record->computeAge() }}</td>
                                <td class="table-cell">{{ $record->gender }}</td>
                            @elseif ($type === ArchiveRecordType::Checkups)
                                <td class="table-cell">
                                    <p class="font-medium text-gray-900">{{ $record->patient?->full_name ?? 'Unknown patient' }}</p>
                                    <p class="text-xs text-gray-500">{{ $record->patient?->patient_code ?? '—' }}</p>
                                </td>
                                <td class="table-cell">{{ $record->visit_date->format('M d, Y') }}</td>
                                <td class="table-cell">{{ Str::limit($record->diagnosis, 35) ?? '—' }}</td>
                            @elseif ($type === ArchiveRecordType::Immunizations)
                                <td class="table-cell font-medium text-gray-900">{{ $record->patient?->full_name ?? 'Unknown patient' }}</td>
                                <td class="table-cell">{{ $record->vaccine_name }}</td>
                                <td class="table-cell">{{ $record->vaccination_date->format('M d, Y') }}</td>
                            @elseif ($type === ArchiveRecordType::MaternalCare)
                                <td class="table-cell font-medium text-gray-900">{{ $record->patient?->full_name ?? 'Unknown patient' }}</td>
                                <td class="table-cell">{{ $record->visit_date->format('M d, Y') }}</td>
                                <td class="table-cell">{{ $record->pregnancy_number }}</td>
                            @else
                                <td class="table-cell font-medium text-gray-900">{{ $record->title }}</td>
                                <td class="table-cell">{{ $record->type->label() }}</td>
                            @endif
                            <td class="table-cell">{{ $record->deleted_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="table-cell">
                                <div class="table-actions">
                                    @if ($type->canRestoreFor(auth()->user()->role))
                                        <form method="POST" action="{{ route('archive.restore', ['type' => $type->value, 'id' => $record->id]) }}" class="inline">
                                            @csrf
                                            <button type="button" data-confirm-restore="Restore this record to the active list?" class="btn-primary text-xs">Restore</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No archived records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
