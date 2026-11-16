@props([
    'name',
    'title',
    'description' => null,
    'maxWidth' => '3xl',
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        '2xl' => 'max-w-2xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        default => 'max-w-3xl',
    };
@endphp

<template x-teleport="body">
<div
    x-cloak
    x-show="$store.modal.active === @js($name)"
    class="modal-root fixed inset-0 z-[80] overflow-y-auto px-4 py-6 sm:px-6"
    @keydown.escape.window="$store.modal.close()"
>
    <div
        x-show="$store.modal.active === @js($name)"
        x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 backdrop-blur-none"
        x-transition:enter-end="opacity-100 backdrop-blur-sm"
        x-transition:leave="transition ease-in duration-250"
        x-transition:leave-start="opacity-100 backdrop-blur-sm"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        class="modal-backdrop-premium fixed inset-0 bg-gray-900/65 backdrop-blur-sm"
        @click="$store.modal.close()"
    ></div>

    <div class="relative mx-auto flex min-h-full items-center justify-center perspective-[1200px]">
        <div
            x-show="$store.modal.active === @js($name)"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-8 scale-90 rotate-x-6"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100 rotate-x-0"
            x-transition:leave="transition ease-in duration-250"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-6 scale-95"
            {{ $attributes->merge(['class' => "modal-panel-premium relative w-full {$maxWidthClass} overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-2xl shadow-primary/10"]) }}
            @click.stop
        >
            <div class="modal-shimmer pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/40 to-transparent"></div>

            <div class="modal-header-premium flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @if ($description)
                        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" class="btn-icon shrink-0" @click="$store.modal.close()">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body-premium max-h-[calc(100vh-8rem)] overflow-y-auto px-6 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
</template>
