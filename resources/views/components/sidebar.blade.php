@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user->role;

    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Users',
            'route' => 'users.index',
            'icon' => 'users',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife],
        ],
        [
            'label' => 'Patients',
            'route' => 'patients.index',
            'icon' => 'patients',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Checkups',
            'route' => 'checkups.index',
            'icon' => 'checkups',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Immunizations',
            'route' => 'immunizations.index',
            'icon' => 'immunizations',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Maternal Care',
            'route' => 'maternal-care.index',
            'icon' => 'maternal',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Announcements',
            'route' => 'announcements.index',
            'icon' => 'announcements',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'SMS',
            'route' => 'sms.index',
            'icon' => 'sms',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife],
        ],
        [
            'label' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'reports',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Archive',
            'route' => 'archive.index',
            'icon' => 'archive',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife, UserRole::Bhw],
        ],
        [
            'label' => 'Activity Logs',
            'route' => 'activity-logs.index',
            'icon' => 'logs',
            'roles' => [UserRole::SystemAdmin, UserRole::Midwife],
        ],
    ];

    $visibleNav = collect($navItems)->filter(fn ($item) => in_array($role, $item['roles'], true));
@endphp

<div class="flex h-full flex-col">
    <div class="flex items-center justify-center border-b border-white/10 px-4 py-5">
        <img
            src="{{ asset('images/logo.png') }}"
            alt="Official Seal of Barangay San Francisco, Talibon, Bohol"
            class="h-[220px] w-[220px] rounded-full bg-white object-cover ring-2 ring-white/30"
        >
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach ($visibleNav as $item)
            @php
                $isActive = request()->routeIs(str_replace('.index', '.*', $item['route']))
                    || request()->routeIs($item['route']);
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([$isActive ? 'nav-link-active' : 'nav-link', 'reveal-stagger'])
                style="--reveal-delay: {{ $loop->index * 55 }}ms"
            >
                @switch($item['icon'])
                    @case('home')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        @break
                    @case('users')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        @break
                    @case('patients')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        @break
                    @case('checkups')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        @break
                    @case('immunizations')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                        </svg>
                        @break
                    @case('maternal')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        @break
                    @case('announcements')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 3.34a1.125 1.125 0 0 1 1.32 0l6.5 4.875a1.125 1.125 0 0 1 0 1.82l-6.5 4.875a1.125 1.125 0 0 1-1.32 0l-6.5-4.875a1.125 1.125 0 0 1 0-1.82l6.5-4.875Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5" />
                        </svg>
                        @break
                    @case('sms')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                        @break
                    @case('reports')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        @break
                    @case('archive')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                        @break
                    @case('logs')
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        @break
                @endswitch
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>
