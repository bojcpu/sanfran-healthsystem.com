<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Barangay health announcements and services for Barangay San Francisco, Talibon, Bohol.">

    <title>{{ $title ?? 'Health Announcements' }} — SanFran HealthSys</title>

    <x-pwa.meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="page-enter min-h-screen bg-gray-50">
    <main class="page-content w-full min-w-0">
        @yield('content')
    </main>

    <footer class="w-full border-t border-gray-200 bg-white pb-[max(1rem,env(safe-area-inset-bottom))]">
        <div class="page-shell py-4 text-center text-xs text-gray-500 sm:py-5">
            <p>&copy; {{ date('Y') }} SanFran HealthSys — Barangay San Francisco, Talibon, Bohol</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
