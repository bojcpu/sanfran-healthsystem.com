@php
    $editingAnnouncement = $editingAnnouncement ?? null;
    $formModalName = $modalName ?? 'new-announcement';
    $announcementFormId = $editingAnnouncement?->id ?? 'new';
@endphp

<form method="POST" action="{{ $editingAnnouncement ? route('announcements.update', $editingAnnouncement) : route('announcements.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($editingAnnouncement)
        @method('PUT')
    @endif
    <input type="hidden" name="_modal" value="{{ $formModalName }}">

    <x-form.input label="Title" name="title" :value="$editingAnnouncement?->title" id="announcement-title-{{ $announcementFormId }}" required />

    <x-form.select
        label="Type"
        name="type"
        :options="collect(\App\Enums\AnnouncementType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
        :value="$editingAnnouncement?->type->value"
        id="announcement-type-{{ $announcementFormId }}"
        placeholder="Select type"
        required
    />

    <x-form.textarea label="Content" name="content" :value="$editingAnnouncement?->content" id="announcement-content-{{ $announcementFormId }}" rows="6" required />

    @if ($editingAnnouncement?->image)
        <div>
            <p class="form-label">Current Image</p>
            <img src="{{ asset('storage/' . $editingAnnouncement->image) }}" alt="{{ $editingAnnouncement->title }}" class="h-32 rounded-lg object-cover">
        </div>
    @endif

    <div>
        <label for="announcement-image-{{ $announcementFormId }}" class="form-label">{{ $editingAnnouncement ? 'Replace Image (optional)' : 'Image (optional)' }}</label>
        <input type="file" name="image" id="announcement-image-{{ $announcementFormId }}" accept="image/*" class="form-input">
        @error('image')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <x-form.select
            label="Status"
            name="status"
            :options="collect(\App\Enums\AnnouncementStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
            :value="$editingAnnouncement?->status->value"
            id="announcement-status-{{ $announcementFormId }}"
            placeholder="Select status"
            required
        />
        <x-form.input label="Publish Date" name="publish_date" type="date" :value="$editingAnnouncement?->publish_date?->format('Y-m-d') ?? now()->format('Y-m-d')" id="announcement-publish-date-{{ $announcementFormId }}" required />
        <x-form.input label="Expiry Date" name="expiry_date" type="date" :value="$editingAnnouncement?->expiry_date?->format('Y-m-d')" id="announcement-expiry-date-{{ $announcementFormId }}" hint="Leave blank for no expiry" />
    </div>

    <div class="flex gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="btn-primary">{{ $editingAnnouncement ? 'Save Changes' : 'Publish' }}</button>
        <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
    </div>
</form>
