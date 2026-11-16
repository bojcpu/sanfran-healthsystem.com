<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Login' }} — SanFran HealthSys</title>

    <x-pwa.meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="page-enter min-h-dvh overflow-x-hidden overflow-y-auto lg:h-dvh lg:overflow-hidden">
    {{-- Multi-purpose hall background --}}
    <div
        class="fixed inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('images/login-hall.jpg') }}');"
        aria-hidden="true"
    ></div>
    <div class="fixed inset-0 bg-primary/30" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-dvh w-full flex-col lg:h-full lg:overflow-hidden">
        <main class="page-content flex min-h-0 w-full flex-1 items-center justify-center px-4 py-3 sm:px-6 sm:py-4 lg:overflow-hidden lg:px-8 lg:py-5 xl:px-12">
            @yield('content')
        </main>

        <footer class="shrink-0 py-2.5 text-center text-xs font-medium text-white drop-shadow-md sm:py-3">
            &copy; {{ date('Y') }} SanFran HealthSys — Barangay San Francisco, Talibon, Bohol
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
