@extends('layouts.app')

@section('content')
    <x-page-header title="Notifications" description="Upcoming patient follow-ups and schedules." />

    <div class="card divide-y divide-gray-100 overflow-hidden">
        @forelse ($notifications as $notification)
            <article class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between {{ $notification->read_at ? 'bg-white' : 'bg-emerald-50/60' }}">
                <div>
                    <p class="font-medium text-gray-900">{{ $notification->data['message'] ?? 'Health record update' }}</p>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $notification->data['date_label'] ?? 'Return date' }}: {{ filled($notification->data['scheduled_date'] ?? $notification->data['follow_up_date'] ?? null) ? \Illuminate\Support\Carbon::parse($notification->data['scheduled_date'] ?? $notification->data['follow_up_date'])->format('M d, Y') : 'Not set' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('notifications.open', $notification->id) }}" class="btn-secondary text-xs">{{ $notification->data['action_label'] ?? 'View Maternal Care' }}</a>
                    @if (! $notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-primary text-xs">Mark read</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="px-4 py-12 text-center text-sm text-gray-500">No notifications yet.</p>
        @endforelse

        @if ($notifications->hasPages())
            <div class="px-4 py-3">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
