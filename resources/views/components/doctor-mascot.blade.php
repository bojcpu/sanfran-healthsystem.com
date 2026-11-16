@php
    $faqCatalog = app(\App\Support\DoctorFaqCatalog::class)->toArray();
@endphp

<div
    class="doctor-mascot-root"
    x-data="doctorMascot(@js($faqCatalog))"
    :class="{
        'is-open': open,
        'is-expanded': expanded,
        'is-walking': walking,
        'is-dragging': dragging,
    }"
    :style="rootStyle"
    @click.self="expanded && toggleExpanded()"
    @click.outside="open && ! expanded && ! dragging && ! walking && closeHelp()"
    data-doctor-mascot
    data-doctor-faq
>
    <template x-teleport="body">
        <div
            class="doctor-drop-marker"
            :class="{ 'is-visible': dragging }"
            x-cloak
            :style="`left: ${dropX}px; top: ${dropY}px`"
            aria-hidden="true"
        ></div>
    </template>
    <div
        class="doctor-faq-panel"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-2 opacity-0"
        role="dialog"
        aria-label="Help questions"
        @click.stop
    >
        <div class="doctor-faq-header">
            <div class="doctor-faq-identity">
                <span class="doctor-faq-avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a7.5 7.5 0 0 1 15 0" />
                    </svg>
                </span>
                <div>
                    <p class="doctor-faq-kicker">Clinic doctor</p>
                    <p class="doctor-faq-title">Ask a question</p>
                </div>
            </div>
            <div class="doctor-faq-actions">
                <button
                    type="button"
                    class="doctor-faq-size"
                    data-no-loading="true"
                    :aria-pressed="expanded.toString()"
                    :aria-label="expanded ? 'Restore question size' : 'Larger view'"
                    :title="expanded ? 'Smaller view' : 'Larger view'"
                    @click="toggleExpanded()"
                >
                    <svg x-show="! expanded" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                    <svg x-show="expanded" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9 4.5 4.5M9 9H4.5M9 9V4.5m6 4.5 4.5-4.5M15 9h4.5M15 9V4.5M9 15l-4.5 4.5M9 15H4.5M9 15v4.5m6-4.5 4.5 4.5M15 15h4.5M15 15v4.5" />
                    </svg>
                    <span x-text="expanded ? 'Smaller' : 'Bigger'"></span>
                </button>
                <button
                    type="button"
                    class="doctor-faq-close"
                    data-no-loading="true"
                    aria-label="Close questions"
                    @click="closeHelp()"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="doctor-faq-toolbar">
            <label class="sr-only" for="doctor-faq-search">Search questions</label>
            <div class="doctor-faq-search-wrap">
                <svg class="doctor-faq-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                </svg>
                <input
                    id="doctor-faq-search"
                    x-ref="search"
                    type="search"
                    class="doctor-faq-search"
                    placeholder="Search… patients, vaccines, news…"
                    autocomplete="off"
                    x-model="query"
                >
            </div>

            <div class="doctor-faq-categories-wrap">
                <button
                    type="button"
                    class="doctor-faq-cat-nav is-prev"
                    data-no-loading="true"
                    x-show="canScrollCatsLeft"
                    x-cloak
                    aria-label="Previous topics"
                    @click="scrollCats(-140)"
                >
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12.5 5-5 5 5 5" />
                    </svg>
                </button>
                <div
                    class="doctor-faq-categories"
                    x-ref="categories"
                    role="tablist"
                    aria-label="FAQ topics"
                    @scroll="updateCatScroll()"
                    @wheel.prevent="onCategoryWheel($event)"
                >
                    <button
                        type="button"
                        class="doctor-faq-chip"
                        data-no-loading="true"
                        :class="{ 'is-active': activeCategory === 'all' }"
                        @click="setCategory('all')"
                    >
                        All
                    </button>
                    <template x-for="category in catalog.categories" :key="category.id">
                        <button
                            type="button"
                            class="doctor-faq-chip"
                            data-no-loading="true"
                            :class="{ 'is-active': activeCategory === category.id }"
                            @click="setCategory(category.id)"
                            x-text="category.label"
                        ></button>
                    </template>
                </div>
                <button
                    type="button"
                    class="doctor-faq-cat-nav is-next"
                    data-no-loading="true"
                    x-show="canScrollCatsRight"
                    x-cloak
                    aria-label="More topics"
                    @click="scrollCats(140)"
                >
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 5 5 5-5 5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="doctor-faq-thread" x-ref="thread">
            <template x-for="message in messages" :key="message.id">
                <div
                    class="doctor-faq-message"
                    :class="message.role === 'user' ? 'is-user' : 'is-doctor'"
                    :data-faq-message="message.id"
                >
                    <p class="doctor-faq-message-label" x-text="message.role === 'user' ? 'You' : 'Doctor'"></p>
                    <p class="doctor-faq-message-text" x-text="message.text"></p>
                    <button
                        type="button"
                        class="doctor-faq-enlarge"
                        data-no-loading="true"
                        x-show="message.role === 'doctor' && messages.length > 1 && ! expanded && message.id === messages[messages.length - 1].id"
                        @click="toggleExpanded()"
                    >
                        Bigger
                    </button>
                    <template x-if="message.role === 'doctor' && message.related && message.related.length">
                        <div class="doctor-faq-related">
                            <p class="doctor-faq-related-label">Related questions</p>
                            <template x-for="related in relatedQuestions(message.related)" :key="related.id">
                                <button
                                    type="button"
                                    class="doctor-faq-question"
                                    data-no-loading="true"
                                    @click="ask(related.id)"
                                    x-text="related.question"
                                ></button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <div class="doctor-faq-list" x-show="showQuestionList">
                <p class="doctor-faq-list-label" x-show="messages.length > 1">More questions</p>
                <template x-if="filteredQuestions.length === 0">
                    <p class="doctor-faq-empty">No matching questions. Try another word, or pick a topic above.</p>
                </template>
                <template x-for="item in filteredQuestions" :key="item.id">
                    <button
                        type="button"
                        class="doctor-faq-question"
                        data-no-loading="true"
                        @click="ask(item.id)"
                        x-text="item.question"
                    ></button>
                </template>
            </div>
        </div>

        <button
            type="button"
            class="doctor-faq-browse"
            data-no-loading="true"
            x-show="messages.length > 1 && query.trim() === ''"
            x-cloak
            @click="browseAll()"
        >
            Browse all questions
        </button>
    </div>

    <button
        type="button"
        class="doctor-mascot"
        x-ref="trigger"
        :class="{
            'is-examining': examining && ! walking,
            'is-walking': walking,
            'is-facing-left': facingLeft,
        }"
        :aria-expanded="open ? 'true' : 'false'"
        data-no-loading="true"
        aria-label="Clinic doctor. Ask a question, or drag to move."
        title="Drag to move. Click to ask a question."
        @pointerdown="onMascotPointerDown($event)"
        @pointermove="onMascotPointerMove($event)"
        @pointerup="onMascotPointerUp($event)"
        @pointercancel="onMascotPointerCancel()"
        @mousedown="onMascotPointerDown($event)"
        @mouseup="onMascotPointerUp($event)"
        @click.prevent="onMascotClick($event)"
    >
        <svg class="doctor-mascot-svg" viewBox="0 0 140 200" overflow="visible" aria-hidden="true">
            <defs>
                <clipPath id="doctor-eye-left">
                    <ellipse cx="58" cy="54" rx="8" ry="9" />
                </clipPath>
                <clipPath id="doctor-eye-right">
                    <ellipse cx="82" cy="54" rx="8" ry="9" />
                </clipPath>
            </defs>

            <ellipse class="doctor-shadow" cx="70" cy="188" rx="36" ry="7" />

            <g class="doctor-figure">
                <g class="doctor-leg doctor-leg-left">
                    <rect x="51" y="136" width="15" height="38" rx="6" fill="#1e3a5f" />
                    <ellipse cx="58.5" cy="176" rx="10" ry="5.5" fill="#111827" />
                </g>
                <g class="doctor-leg doctor-leg-right">
                    <rect x="74" y="136" width="15" height="38" rx="6" fill="#1e3a5f" />
                    <ellipse cx="81.5" cy="176" rx="10" ry="5.5" fill="#111827" />
                </g>

                <g class="doctor-arm doctor-arm-left">
                    <path d="M46 96c-10 10-14 24-12 36" fill="none" stroke="#f8fafc" stroke-linecap="round" stroke-width="11" />
                    <path d="M46 96c-10 10-14 24-12 36" fill="none" stroke="#e8b896" stroke-linecap="round" stroke-width="7" />
                    <circle cx="34" cy="134" r="6.5" fill="#e8b896" />
                </g>

                <g class="doctor-torso">
                    <rect x="42" y="86" width="56" height="58" rx="16" fill="#f8fafc" stroke="#d1d5db" stroke-width="1.5" />
                    <path d="M70 90v50" fill="none" stroke="#e5e7eb" stroke-width="1.5" />
                    <path d="M42 102c8-8 18-12 28-12s20 4 28 12" fill="none" stroke="#d1d5db" stroke-width="1.5" />
                    <rect x="78" y="112" width="14" height="12" rx="2" fill="#fff" stroke="#d1d5db" />
                    <path d="M85 114.5v7M81.5 118h7" stroke="#094F29" stroke-linecap="round" stroke-width="2" />
                    <path d="M64 96v18" fill="none" stroke="#094F29" stroke-width="3.5" stroke-linecap="round" />
                    <path d="M61 96h6" fill="none" stroke="#094F29" stroke-width="3" stroke-linecap="round" />
                </g>

                <g class="doctor-arm doctor-arm-right">
                    <path d="M94 96c10 8 16 22 14 36" fill="none" stroke="#f8fafc" stroke-linecap="round" stroke-width="11" />
                    <path d="M94 96c10 8 16 22 14 36" fill="none" stroke="#e8b896" stroke-linecap="round" stroke-width="7" />
                    <path d="M86 92c8 8 14 20 20 36" fill="none" stroke="#4b5563" stroke-width="2.4" stroke-linecap="round" />
                    <circle cx="108" cy="134" r="6.5" fill="#e8b896" />
                    <g class="doctor-stethoscope-head">
                        <circle cx="108" cy="145" r="6" fill="#dc2626" stroke="#7f1d1d" stroke-width="1.2" />
                        <circle cx="108" cy="145" r="2.4" fill="#fecaca" />
                    </g>
                </g>

                <g class="doctor-stethoscope-neck">
                    <path d="M54 92c-8 8-10 18-6 28" fill="none" stroke="#4b5563" stroke-width="2.4" stroke-linecap="round" />
                    <path d="M54 92c4-6 12-10 16-10s12 4 16 10" fill="none" stroke="#4b5563" stroke-width="2.4" stroke-linecap="round" />
                </g>

                <g class="doctor-head" x-ref="face">
                    <circle cx="48" cy="58" r="7" fill="#e8b896" />
                    <circle cx="92" cy="58" r="7" fill="#e8b896" />
                    <circle cx="70" cy="56" r="28" fill="#e8b896" />
                    <path d="M44 48c4-18 18-26 26-26 10 0 22 8 26 24-8-8-16-10-26-10s-18 2-26 12Z" fill="#2c1810" />
                    <path d="M54 46c4-3 8-4 16-4 8 0 13 1 17 4" fill="none" stroke="#1a0f0a" stroke-width="2.2" stroke-linecap="round" />

                    <g clip-path="url(#doctor-eye-left)">
                        <ellipse cx="58" cy="54" rx="8" ry="9" fill="#fff" />
                        <g class="doctor-pupil" :transform="`translate(${lookX} ${lookY})`">
                            <circle cx="58" cy="56" r="3.4" fill="#1f2937" />
                            <circle cx="56.6" cy="54.6" r="1.1" fill="#fff" />
                        </g>
                    </g>
                    <rect class="doctor-eyelid" x="50" y="45" width="16" height="18" rx="8" fill="#e8b896" />

                    <g clip-path="url(#doctor-eye-right)">
                        <ellipse cx="82" cy="54" rx="8" ry="9" fill="#fff" />
                        <g class="doctor-pupil" :transform="`translate(${lookX} ${lookY})`">
                            <circle cx="82" cy="56" r="3.4" fill="#1f2937" />
                            <circle cx="80.6" cy="54.6" r="1.1" fill="#fff" />
                        </g>
                    </g>
                    <rect class="doctor-eyelid" x="74" y="45" width="16" height="18" rx="8" fill="#e8b896" />

                    <path d="M63 70c3 4 11 4 14 0" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" />
                </g>
            </g>
        </svg>
    </button>
</div>
