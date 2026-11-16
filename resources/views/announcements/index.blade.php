@extends('layouts.app')

@section('content')
    @php
        $canManage = auth()->user()->hasRole(\App\Enums\UserRole::SystemAdmin, \App\Enums\UserRole::Midwife);
    @endphp

    <x-page-header title="Announcements" description="Health advisories and barangay announcements.">
        @if ($canManage)
            <x-slot:actions>
                <button type="button" class="btn-primary" @click="$store.modal.open('new-announcement')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Announcement
                </button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="space-y-8">
        @forelse ($typeOrder as $typeValue => $typeLabel)
            @php
                $items = $announcementsByType->get($typeValue, collect());
            @endphp
            @if ($items->isNotEmpty())
                <section>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900">
                            <x-announcement-type-icon :type="\App\Enums\AnnouncementType::from($typeValue)" @class(['h-5 w-5', \App\Enums\AnnouncementType::from($typeValue)->iconClass()]) />
                            {{ $typeLabel }}
                        </h2>
                        <span class="badge-primary">{{ $items->count() }} {{ Str::plural('announcement', $items->count()) }}</span>
                    </div>
                    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $announcement)
                            <article
                                id="announcement-{{ $announcement->id }}"
                                @class(['card overflow-hidden', 'notification-highlight' => (string) request('highlight') === (string) $announcement->id])
                            >
                                @if ($announcement->image)
                                    <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="h-40 w-full object-cover">
                                @else
                                    <div class="flex h-40 items-center justify-center bg-gray-50" aria-hidden="true">
                                        <x-announcement-type-icon :type="$announcement->type" @class(['h-12 w-12', $announcement->type->iconClass()]) />
                                    </div>
                                @endif
                                <div class="p-5">
                                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="{{ $announcement->type->badgeClass() }} inline-flex items-center gap-1.5">
                                                <x-announcement-type-icon :type="$announcement->type" class="h-3.5 w-3.5" />
                                                {{ $announcement->type->label() }}
                                            </span>
                                            @if ($announcement->status->value === 'active')
                                                <span class="badge-success">{{ $announcement->status->label() }}</span>
                                            @else
                                                <span class="badge-danger">{{ $announcement->status->label() }}</span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $announcement->publish_date->format('M d, Y') }}</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $announcement->title }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $announcement->content }}</p>
                                    <p class="mt-3 text-xs text-gray-400">Posted by {{ $announcement->postedBy?->name ?? 'System' }}.</p>
                                    @if ($canManage)
                                        <div class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
                                            <button type="button" class="btn-secondary flex-1 text-xs" @click="$store.modal.open('edit-announcement-{{ $announcement->id }}')">Edit</button>
                                            <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" class="flex-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" data-confirm-archive="Archive this announcement?" class="btn-primary w-full text-xs">Archive</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if ($announcementsByType->isEmpty())
            <div class="card p-12 text-center">
                <p class="text-sm text-gray-500">No announcements available.</p>
            </div>
        @endif
    </div>

    @if ($canManage)
        <x-modal name="new-announcement" title="New Announcement" description="Publish a health advisory or announcement." maxWidth="3xl">
            @include('announcements.partials.form')
        </x-modal>

        @foreach ($announcementsByType as $items)
            @foreach ($items as $announcement)
                @php $modalName = 'edit-announcement-' . $announcement->id; @endphp
                <x-modal :name="$modalName" title="Edit Announcement" :description="$announcement->title" maxWidth="3xl">
                    @include('announcements.partials.form', ['editingAnnouncement' => $announcement, 'modalName' => $modalName])
                </x-modal>
            @endforeach
        @endforeach
    @endif
@endsection
