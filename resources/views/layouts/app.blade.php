<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} — SanFran HealthSys</title>

    <x-pwa.meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="page-enter min-h-screen bg-gray-50" x-data="{ sidebarOpen: false }">
    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
        x-cloak
        @click="sidebarOpen = false"
    ></div>

    <div class="app-shell">
        {{-- Sidebar --}}
        <aside
            class="app-sidebar"
            :class="{ 'is-open': sidebarOpen }"
        >
            <x-sidebar />
        </aside>

        {{-- Main content --}}
        <div class="app-main">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 border-b border-gray-200 bg-white shadow-sm">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="btn-icon lg:hidden"
                            @click="sidebarOpen = !sidebarOpen"
                        >
                            <span class="sr-only">Toggle sidebar</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-primary">BARANGAY SAN FRANCISCO</p>
                            <p class="text-sm font-normal text-gray-900">Health Center System</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (auth()->user()->isMidwife())
                            @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
                            <a
                                href="{{ route('notifications.index') }}"
                                class="btn-icon relative"
                                aria-label="Notifications"
                                title="Notifications"
                                data-staff-notification-count-url="{{ route('notifications.unread-count') }}"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 0 0-12 0v.75a8.967 8.967 0 0 1-2.31 6.022c1.8.714 3.71 1.17 5.67 1.34m5.497-.03a24.255 24.255 0 0 1-5.497.03m5.497-.03a3 3 0 1 1-5.497.03" />
                                </svg>
                                <span
                                    data-staff-notification-count
                                    @class([
                                        'absolute -right-1 -top-1 h-5 min-w-5 items-center justify-center rounded-full border border-black bg-white px-1 text-[10px] font-bold text-black shadow-sm',
                                        'flex' => $unreadNotificationCount > 0,
                                        'hidden' => $unreadNotificationCount === 0,
                                    ])
                                >{{ $unreadNotificationCount }}</span>
                            </a>
                        @endif
                        <x-profile-dropdown />
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="page-content flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <x-alert type="success" :message="session('success')" class="mb-6" />
                @endif

                @if (session('error'))
                    <x-alert type="error" :message="session('error')" class="mb-6" />
                @endif

                @if (session('warning'))
                    <x-alert type="warning" :message="session('warning')" class="mb-6" />
                @endif

                @if (session('info'))
                    <x-alert type="info" :message="session('info')" class="mb-6" />
                @endif

                @if ($errors->any())
                    <x-alert type="error" class="mb-6">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-gray-200 bg-white px-4 py-4 text-center text-xs text-gray-500 sm:px-6 lg:px-8">
                &copy; {{ date('Y') }} SanFran HealthSys — Barangay San Francisco, Talibon, Bohol
            </footer>
        </div>
    </div>

    <x-modal
        name="profile-settings"
        title="Account Settings"
        description="Update your profile picture and password."
        maxWidth="lg"
    >
        @include('profile.partials.settings-form')
    </x-modal>

    <x-doctor-mascot />

    @stack('scripts')

    @if (old('_modal'))
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('modal').open(@json(old('_modal')));
            });
        </script>
    @endif
</body>
</html>
