@extends('layouts.app')

@section('content')
    <x-page-header title="New Announcement" description="Publish a health advisory or announcement.">
        <x-slot:actions>
            <a href="{{ route('announcements.index') }}" class="btn-secondary">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-form.input label="Title" name="title" required />

            <x-form.select
                label="Type"
                name="type"
                :options="collect(\App\Enums\AnnouncementType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
                placeholder="Select type"
                required
            />

            <x-form.textarea label="Content" name="content" rows="6" required />

            <div>
                <label for="image" class="form-label">Image (optional)</label>
                <input type="file" name="image" id="image" accept="image/*" class="form-input">
                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <x-form.select
                    label="Status"
                    name="status"
                    :options="collect(\App\Enums\AnnouncementStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
                    placeholder="Select status"
                    required
                />
                <x-form.input label="Publish Date" name="publish_date" type="date" :value="old('publish_date', now()->format('Y-m-d'))" required />
                <x-form.input label="Expiry Date" name="expiry_date" type="date" hint="Leave blank for no expiry" />
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Publish</button>
                <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
