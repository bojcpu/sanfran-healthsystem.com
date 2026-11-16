@props([
    'label',
    'name',
    'value' => null,
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'rows' => 4,
    'hint' => null,
])

<div>
    <label for="{{ $id ?? $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <textarea
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'form-textarea' . ($errors->has($name) ? ' border-red-500 focus:border-red-500 focus:ring-red-500/20' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
