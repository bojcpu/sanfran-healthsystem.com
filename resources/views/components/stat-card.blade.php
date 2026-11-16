@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendLabel' => null,
    'color' => 'primary',
])

@php
    $colorClasses = match ($color) {
        'green' => 'bg-green-50 text-green-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        default => 'bg-primary-50 text-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card reveal-on-scroll reveal-scale card-interactive p-5']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="reveal-icon-pop text-sm font-medium text-gray-500">{{ $title }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $value }}</p>
            @if ($trend !== null)
                <p class="mt-1 text-xs text-gray-500">
                    <span @class([
                        'font-semibold',
                        str_starts_with((string) $trend, '+') || str_starts_with((string) $trend, '↑') ? 'text-green-600' : 'text-red-600',
                    ])>{{ $trend }}</span>
                    @if ($trendLabel)
                        <span>{{ $trendLabel }}</span>
                    @endif
                </p>
            @endif
        </div>
        @if ($icon)
            <div @class(['reveal-icon-pop flex h-12 w-12 items-center justify-center rounded-xl', $colorClasses])>
                {!! $icon !!}
            </div>
        @elseif (isset($iconSlot))
            <div @class(['reveal-icon-pop flex h-12 w-12 items-center justify-center rounded-xl', $colorClasses])>
                {{ $iconSlot }}
            </div>
        @endif
    </div>
</div>
