@props([
    'user' => auth()->user(),
    'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary font-bold text-white ring-2 ring-primary/20 {$sizeClasses}"]) }}>
    @if ($user->profilePictureUrl())
        <img
            src="{{ $user->profilePictureUrl() }}"
            alt="{{ $user->name }}"
            class="h-full w-full object-cover"
        >
    @else
        {{ $user->initials() }}
    @endif
</div>
