@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'showPasswordToggle' => false,
])

<div>
    <label for="{{ $id ?? $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    @if ($showPasswordToggle)
        <div x-data="{ passwordVisible: false }" class="relative">
    @endif
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id ?? $name }}"
            value="{{ old($name, $value) }}"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($disabled) disabled @endif
            @if ($showPasswordToggle) x-bind:type="passwordVisible ? 'text' : 'password'" @endif
            {{ $attributes->merge(['class' => 'form-input' . ($errors->has($name) ? ' border-red-500 focus:border-red-500 focus:ring-red-500/20' : '') . ($showPasswordToggle ? ' pr-12' : '')]) }}
        >
    @if ($showPasswordToggle)
            <button
                type="button"
                x-on:click="passwordVisible = !passwordVisible"
                class="absolute right-1 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                x-bind:aria-label="passwordVisible ? 'Hide password' : 'Show password'"
                x-bind:aria-pressed="passwordVisible"
                x-bind:title="passwordVisible ? 'Hide password' : 'Show password'"
            >
                <svg x-show="!passwordVisible" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12s-3.5 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <svg x-show="passwordVisible" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a2 2 0 0 0 2.83 2.83M9.88 5.09A10.75 10.75 0 0 1 12 4.88c6.25 0 9.75 7.12 9.75 7.12a17.4 17.4 0 0 1-3.04 3.96M6.23 6.23C3.68 7.87 2.25 12 2.25 12s3.5 7.12 9.75 7.12c1.39 0 2.64-.36 3.74-.94" />
                </svg>
            </button>
        </div>
    @endif
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
