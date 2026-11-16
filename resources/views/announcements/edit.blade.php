@extends('layouts.app')

@section('content')
    <x-page-header title="Edit Announcement" :description="$announcement->title">
        <x-slot:actions>
            <a href="{{ route('announcements.index') }}" class="btn-secondary">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('announcements.update', $announcement) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.input label="Title" name="title" :value="$announcement->title" required />

            <x-form.select
                label="Type"
                name="type"
                :options="collect(\App\Enums\AnnouncementType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all()"
                :value="$announcement->type->value"
                placeholder="Select type"
                required
            />

            <x-form.textarea label="Content" name="content" :value="$announcement->content" rows="6" required />

            @if ($announcement->image)
                <div>
                    <p class="form-label">Current Image</p>
                    <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="h-32 rounded-lg object-cover">
                </div>
            @endif

            <div>
                <label for="image" class="form-label">Replace Image (optional)</label>
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
                    :value="$announcement->status->value"
                    required
                />
                <x-form.input label="Publish Date" name="publish_date" type="date" :value="$announcement->publish_date->format('Y-m-d')" required />
                <x-form.input label="Expiry Date" name="expiry_date" type="date" :value="$announcement->expiry_date?->format('Y-m-d')" />
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
