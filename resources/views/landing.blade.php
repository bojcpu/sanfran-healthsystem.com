@extends('layouts.public')

@section('content')
    @php
        $queryWithoutType = array_filter(['sort' => $currentSort === 'oldest' ? 'oldest' : null]);
        $queryWithoutSort = array_filter(['type' => $currentType?->value]);
        $announcementGroups = $currentType === null && $announcementsByType->isNotEmpty()
            ? collect(\App\Enums\AnnouncementType::cases())->mapWithKeys(fn ($type) => [$type->value => $announcementsByType->get($type->value, collect())])
            : $announcementsByType;
    @endphp

    <section class="landing-hero relative isolate w-full overflow-hidden" style="aspect-ratio: 6 / 1; min-height: 80px;">
        <div class="absolute inset-0" aria-hidden="true">
            <svg class="h-full w-full" viewBox="0 0 1024 171" preserveAspectRatio="xMidYMin slice">
                <image href="{{ asset('images/landing-hall.jpg') }}" width="1024" height="519" preserveAspectRatio="none" />
            </svg>
            <div class="absolute inset-0" style="background-color: rgb(9 79 41 / 45%);"></div>
        </div>
        <div class="relative page-shell pt-[max(0.75rem,env(safe-area-inset-top))]">
            <div class="flex items-center justify-between gap-2 py-2.5 sm:gap-3 sm:py-4">
                <div class="landing-header-brand flex min-w-0 items-center gap-2 text-white sm:gap-3">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center text-white">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Barangay San Francisco seal"
                            width="88"
                            height="88"
                            class="h-12 w-12 shrink-0 rounded-full bg-white object-cover ring-2 ring-white/70 sm:h-[88px] sm:w-[88px]"
                        >
                    </a>
                    <span class="flex min-w-0 flex-col">
                        <span class="landing-header-brand-name font-bold tracking-wide text-white">
                            SanFran HealthSys
                        </span>
                        <span class="landing-header-brand-subtitle font-medium text-primary-100">
                            Barangay Health Center
                        </span>
                    </span>
                </div>

                <div class="landing-header-actions flex shrink-0 items-center gap-1 sm:gap-2">
                    <x-pwa.install-button class="landing-header-button btn-accent min-h-11 whitespace-nowrap border border-white/30 bg-white/95 px-2.5 py-2 text-xs text-primary shadow-sm sm:min-h-0 sm:px-4 sm:py-2.5 sm:text-sm" />
                    @auth
                        <a href="{{ route('dashboard') }}" class="landing-header-button btn-accent min-h-11 whitespace-nowrap px-2.5 py-2 text-xs text-primary shadow-sm sm:min-h-0 sm:px-4 sm:py-2.5 sm:text-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="landing-header-button btn-accent min-h-11 whitespace-nowrap px-3 py-2 text-xs text-primary shadow-sm sm:min-h-0 sm:px-4 sm:py-2.5 sm:text-sm">
                            Login
                        </a>
                    @endauth
                </div>
            </div>

        </div>
    </section>

    <section
        class="page-shell pt-5 pb-[max(8rem,calc(env(safe-area-inset-bottom)+7.5rem))] sm:pt-8 sm:pb-32"
        x-data="{ selectedAnnouncement: { type: '', title: '', content: '', date: '', author: '', image: '' } }"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex min-w-0 items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold uppercase text-gray-900 sm:text-xl">PUBLIC ANNOUNCEMENT</h1>
                    <h2 class="mt-1 text-base font-normal text-gray-900">Health announcements for San Francisco.</h2>
                    <p class="mt-1 max-w-3xl text-sm text-gray-500">
                        News about vaccines, care for mothers, and health events in Barangay San Francisco, Talibon, Bohol.
                    </p>
                </div>
                <div
                    class="flex shrink-0 items-center gap-1.5 sm:gap-2"
                    data-announcement-push-widget
                    data-public-key-url="{{ route('push.public-key') }}"
                    data-subscribe-url="{{ route('push.subscribe') }}"
                    data-unsubscribe-url="{{ route('push.unsubscribe') }}"
                    data-unread-url="{{ route('push.unread-count') }}"
                    data-mark-read-url="{{ route('push.mark-read') }}"
                    data-public-unread-url="{{ route('public-announcements.unread-count') }}"
                >
                    <a href="{{ route('home') }}" class="btn-accent relative h-11 w-11 p-2" data-announcement-bell title="View latest announcement" aria-label="View latest announcement">
                        <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.31 6.022c1.8.714 3.71 1.17 5.67 1.34m5.497-.03a24.255 24.255 0 0 1-5.497.03m5.497-.03a3 3 0 1 1-5.497.03" />
                        </svg>
                        <span class="absolute -right-1 -top-1 hidden h-5 min-w-5 items-center justify-center rounded-full border border-white bg-primary px-1 text-[10px] font-bold text-white" data-announcement-unread-count aria-live="polite">0</span>
                    </a>
                    <button type="button" class="btn-secondary relative h-11 w-11 p-2" data-announcement-push-toggle-button data-no-loading="true" title="Enable offline alerts" aria-label="Enable offline alerts">
                        <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2 8a10 10 0 0 1 2-5m18 5a10 10 0 0 0-2-5" />
                        </svg>
                    </button>
                    <button type="button" class="btn-accent hidden h-11 w-11 p-2" data-announcement-mark-read data-no-loading="true" title="Mark announcement alerts as read" aria-label="Mark announcement alerts as read">
                        <svg class="mx-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 18 12-12M6 6l12 12" />
                        </svg>
                    </button>
                    <span class="sr-only" data-announcement-push-status role="status" aria-live="polite"></span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap" role="group" aria-label="Sort announcements by date">
                <a
                    href="{{ route('home', $queryWithoutSort) }}"
                    class="{{ $currentSort === 'latest' ? 'btn-primary' : 'btn-secondary' }} min-h-11 px-3 py-2.5 text-sm"
                >
                    Newest first
                </a>
                <a
                    href="{{ route('home', array_merge($queryWithoutSort, ['sort' => 'oldest'])) }}"
                    class="{{ $currentSort === 'oldest' ? 'btn-primary' : 'btn-secondary' }} min-h-11 px-3 py-2.5 text-sm"
                >
                    Oldest first
                </a>
            </div>
        </div>

        <div class="announcement-filter-list chip-scroll mt-4 sm:mt-5" role="tablist" aria-label="Filter announcements by type">
            <a
                href="{{ route('home') }}"
                class="{{ $currentType === null ? 'filter-chip-active' : 'filter-chip' }} filter-chip-all"
                @if ($currentType === null) aria-current="page" @endif
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 1.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Recent
            </a>
            @foreach ($types as $type)
                <a
                    href="{{ route('home', array_filter(['type' => $type->value, 'sort' => $currentSort === 'oldest' ? 'oldest' : null])) }}"
                    class="{{ $currentType === $type ? 'filter-chip-active' : 'filter-chip' }} {{ $type->chipClass() }}"
                    @if ($currentType === $type) aria-current="page" @endif
                >
                    <x-announcement-type-icon :type="$type" @class(['h-4 w-4', $type->iconClass()]) />
                    {{ $type->label() }}
                </a>
            @endforeach
        </div>

        <div class="announcement-sections-grid mt-5 sm:mt-8">
            @forelse ($announcementGroups as $typeValue => $announcements)
                @php
                    $sectionType = \App\Enums\AnnouncementType::from($typeValue);
                @endphp
                <section class="min-w-0 {{ $currentType !== null ? 'sm:col-span-3' : '' }}" aria-labelledby="type-{{ $sectionType->value }}">
                    <div class="mb-2 flex items-center gap-2">
                        <h3 id="type-{{ $sectionType->value }}" class="flex items-center gap-1.5 text-sm font-semibold text-gray-900">
                            <x-announcement-type-icon :type="$sectionType" @class(['h-4 w-4', $sectionType->iconClass()]) />
                            {{ $sectionType->label() }}
                        </h3>
                        <span class="{{ $sectionType->badgeClass() }}">{{ $announcements->count() }}</span>
                    </div>
                    <div class="announcement-grid {{ $currentType !== null ? 'announcement-grid-filtered' : '' }}">
                        @foreach ($announcements as $announcement)
                            <article
                                id="announcement-{{ $announcement->id }}"
                                class="card group flex h-80 cursor-pointer flex-col overflow-hidden text-left transition hover:-translate-y-0.5 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                                role="button"
                                tabindex="0"
                                aria-label="View announcement: {{ $announcement->title }}"
                                @if ($openAnnouncementId === $announcement->id)
                                    data-open-announcement
                                    x-init="$nextTick(() => $el.click())"
                                @endif
                                @click="selectedAnnouncement = {
                                    type: @js($announcement->type->label()),
                                    typeClass: @js($announcement->type->badgeClass()),
                                    title: @js($announcement->title),
                                    content: @js($announcement->content),
                                    date: @js($announcement->publish_date->format('M d, Y')),
                                    author: @js($announcement->postedBy?->name ?? 'Barangay Health Center'),
                                    image: @js($announcement->image ? asset('storage/' . $announcement->image) : ''),
                                }; $store.modal.open('announcement-details')"
                                @keydown.enter.prevent="$el.click()"
                                @keydown.space.prevent="$el.click()"
                            >
                                @if ($announcement->image)
                                    <img src="{{ asset('storage/' . $announcement->image) }}" alt="" class="h-44 w-full shrink-0 object-cover">
                                @else
                                    <div class="flex h-44 shrink-0 items-center justify-center bg-gray-50" aria-hidden="true">
                                        <x-announcement-type-icon :type="$announcement->type" @class(['h-12 w-12', $announcement->type->iconClass()]) />
                                    </div>
                                @endif
                                <div class="flex min-h-0 flex-1 flex-col p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="{{ $announcement->type->badgeClass() }} inline-flex items-center gap-1.5">
                                            <x-announcement-type-icon :type="$announcement->type" class="h-3.5 w-3.5" />
                                            {{ $announcement->type->label() }}
                                        </span>
                                        <time class="text-xs text-gray-500" datetime="{{ $announcement->publish_date->toDateString() }}">
                                            {{ $announcement->publish_date->format('M d, Y') }}
                                        </time>
                                    </div>
                                    <h4 class="mt-2 line-clamp-2 text-sm font-semibold leading-snug text-gray-900">{{ $announcement->title }}</h4>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-600">{{ $announcement->content }}</p>
                                    <p class="mt-auto pt-2 text-xs text-gray-400">Posted by {{ $announcement->postedBy?->name ?? 'Barangay Health Center' }}.</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="card p-8 text-center sm:p-12">
                    <p class="text-sm text-gray-500">No news right now. Please check back later.</p>
                </div>
            @endforelse
        </div>

        <x-modal name="announcement-details" title="Announcement Details" maxWidth="2xl">
            <div class="space-y-4">
                <img
                    x-show="selectedAnnouncement.image"
                    :src="selectedAnnouncement.image"
                    :alt="selectedAnnouncement.title"
                    class="max-h-80 w-full rounded-xl object-cover"
                >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="badge" x-bind:class="selectedAnnouncement.typeClass" x-text="selectedAnnouncement.type"></span>
                    <time class="text-sm text-gray-500" x-text="selectedAnnouncement.date"></time>
                </div>
                <h2 class="text-xl font-semibold text-gray-900" x-text="selectedAnnouncement.title"></h2>
                <p class="whitespace-pre-wrap break-words text-sm leading-6 text-gray-700" x-text="selectedAnnouncement.content"></p>
                <p class="border-t border-gray-100 pt-3 text-xs text-gray-500">
                    Posted by <span x-text="selectedAnnouncement.author"></span>.
                </p>
            </div>
        </x-modal>
    </section>
@endsection
