@extends('layouts.app')

@section('content')
    <x-page-header title="Activity Logs" description="System audit trail and user activity history." />

    <div class="card mb-6 p-4">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <x-record-search-input source="activity-logs" :value="request('search')" placeholder="Search action or description..." />
            <select name="module" class="form-select sm:w-48">
                <option value="">All Modules</option>
                @foreach ($modules ?? [] as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>{{ Str::headline($module) }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="form-input sm:w-40">
            <button type="submit" class="btn-primary sm:self-end">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="table-header table-cell">Timestamp</th>
                        <th class="table-header table-cell">User</th>
                        <th class="table-header table-cell">Action</th>
                        <th class="table-header table-cell">Module</th>
                        <th class="table-header table-cell">Description</th>
                        <th class="table-header table-cell">Device</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($activityLogs as $log)
                        <tr>
                            <td class="table-cell whitespace-nowrap text-xs">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            <td class="table-cell">
                                <span class="font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</span>
                                @if ($log->user)
                                    <p class="text-xs text-gray-500">{{ $log->user->role->label() }}</p>
                                @endif
                            </td>
                            <td class="table-cell">
                                <span class="badge-primary">{{ $log->action }}</span>
                            </td>
                            <td class="table-cell">{{ Str::headline($log->module ?? '—') }}</td>
                            <td class="table-cell max-w-md truncate">{{ $log->description }}</td>
                            <td class="table-cell text-xs">{{ $log->device_name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No activity logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activityLogs->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $activityLogs->links() }}</div>
        @endif
    </div>
@endsection
