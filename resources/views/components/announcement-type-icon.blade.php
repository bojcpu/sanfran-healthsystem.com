@props([
    'type',
    'class' => 'h-5 w-5',
])

<svg {{ $attributes->merge(['class' => $class]) }} fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
    @if ($type === \App\Enums\AnnouncementType::Emergency)
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.3 3.86 2.92 16.64A2 2 0 0 0 4.65 19.6h14.7a2 2 0 0 0 1.73-2.96L13.7 3.86a2 2 0 0 0-3.46 0Z" />
    @elseif ($type === \App\Enums\AnnouncementType::Advisory)
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v10m-5-5h10" />
    @elseif ($type === \App\Enums\AnnouncementType::Event)
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 3v3m8-3v3M4.5 9h15M6 5h12a1.5 1.5 0 0 1 1.5 1.5v12A1.5 1.5 0 0 1 18 20H6a1.5 1.5 0 0 1-1.5-1.5v-12A1.5 1.5 0 0 1 6 5Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="m9 15 2 2 4-4" />
    @elseif ($type === \App\Enums\AnnouncementType::Immunization)
        <path stroke-linecap="round" stroke-linejoin="round" d="m14 4 6 6M12.5 5.5l6 6m-8.6-.6 5.7 5.7m-2.9-8.5-8.5 8.5a2.1 2.1 0 0 0 3 3l8.5-8.5m-11.5 5.5-2.5 2.5M4 20l-1 1m5-5-3-3" />
    @elseif ($type === \App\Enums\AnnouncementType::MaternalCare)
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.3-7-9.5A4 4 0 0 1 12 9a4 4 0 0 1 7 2.5C19 16.7 12 21 12 21Z" />
        <circle cx="12" cy="13" r="1.25" />
    @else
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10v4a1 1 0 0 0 1 1h2l8 4V5l-8 4H4a1 1 0 0 0-1 1Zm11-3.5a8 8 0 0 1 0 11M6 15l1.5 5h3L9 16.5" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9a4 4 0 0 1 0 6" />
    @endif
</svg>
