@props([
    'source',
    'value' => '',
    'placeholder' => 'Search records...',
    'inputClass' => 'form-input flex-1',
    'context' => null,
])

@php
    $routeParameters = array_filter([
        'source' => $source,
        'context' => $context,
    ], fn ($parameter) => $parameter !== null);
    $suggestionsUrl = route('search.suggestions', $routeParameters, false);
    $suggestionListId = 'record-search-suggestions-' . \Illuminate\Support\Str::uuid();
@endphp

<div
    class="relative flex-1"
    x-data="recordSearchSuggestions(@js($suggestionsUrl))"
    @click.outside="close()"
>
    <input
        x-ref="search"
        type="search"
        name="search"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="{{ $inputClass }}"
        {{ $attributes }}
        autocomplete="off"
        role="combobox"
        aria-autocomplete="list"
        aria-haspopup="listbox"
        aria-controls="{{ $suggestionListId }}"
        :aria-expanded="open && suggestions.length > 0"
        @input.debounce.250ms="fetchSuggestions($event.target.value)"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        @keydown.enter="selectActive($event)"
        @keydown.escape="close()"
    >

    <ul
        id="{{ $suggestionListId }}"
        x-cloak
        x-show="open && suggestions.length > 0"
        role="listbox"
        class="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
    >
        <template x-for="(suggestion, index) in suggestions" :key="suggestion.label">
            <li role="presentation">
                <button
                    type="button"
                    role="option"
                    :aria-selected="activeIndex === index"
                    :class="activeIndex === index ? 'bg-primary-50 text-primary' : 'text-gray-700'"
                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50"
                    @mouseenter="activeIndex = index"
                    @mousedown.prevent="select(suggestion)"
                    x-text="suggestion.label"
                ></button>
            </li>
        </template>
    </ul>

    <p
        x-cloak
        x-show="error"
        role="status"
        class="absolute z-30 mt-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600 shadow-lg"
    >Suggestions could not be loaded. You can still submit your search.</p>
</div>
