@props([
    'label',
    'name',
    'options' => [],
    'value' => null,
    'id' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'hint' => null,
])

<div>
    <label for="{{ $id ?? $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <select
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'form-select' . ($errors->has($name) ? ' border-red-500 focus:border-red-500 focus:ring-red-500/20' : '')]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
