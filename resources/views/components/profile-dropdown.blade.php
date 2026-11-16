@php
    $user = auth()->user();
@endphp

<div
    x-data="{ open: false }"
    class="relative"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="profile-trigger group flex items-center gap-3 rounded-xl px-2 py-1.5 transition focus:outline-none focus:ring-2 focus:ring-primary/25 sm:px-3"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
    >
        <div class="hidden text-right sm:block">
            <p class="text-sm font-semibold text-gray-900 transition group-hover:text-primary">{{ $user->name }}</p>
            <p class="text-xs text-gray-500">{{ $user->role->label() }}</p>
        </div>
        <x-user-avatar :user="$user" class="profile-avatar-ring transition group-hover:scale-105" />
        <svg
            class="hidden h-4 w-4 text-gray-400 transition group-hover:text-primary sm:block"
            :class="{ 'rotate-180': open }"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        @click.outside="open = false"
        class="profile-dropdown absolute right-0 z-50 mt-2 w-56 origin-top-right overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
        x-cloak
    >
        <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
            <p class="truncate text-sm font-semibold text-gray-900">{{ $user->name }}</p>
            <p class="text-xs text-gray-500">{{ $user->role->label() }}</p>
        </div>

        <div class="p-1.5">
            <button
                type="button"
                class="profile-dropdown-item w-full"
                @click="$store.modal.open('profile-settings'); open = false"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Settings
            </button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="profile-dropdown-item profile-dropdown-item-danger w-full"
                    data-confirm-logout="You will be signed out of SanFran HealthSys."
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Log out
                </button>
            </form>
        </div>
    </div>
</div>
