<form
    method="POST"
    action="{{ route('profile.update') }}"
    enctype="multipart/form-data"
    data-loading
    x-data="{
        preview: @js(auth()->user()->profilePictureUrl()),
        removePicture: false,
        onFileChange(event) {
            const file = event.target.files[0];
            if (! file) return;
            this.removePicture = false;
            this.preview = URL.createObjectURL(file);
        },
    }"
>
    @csrf
    @method('PUT')
    <input type="hidden" name="_modal" value="profile-settings">

    <div class="space-y-6">
        <div class="reveal-on-scroll reveal-scale">
            <label class="form-label">Profile Picture</label>
            <div class="flex flex-col items-center gap-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-start">
                <div class="relative">
                    <template x-if="preview">
                        <img :src="preview" alt="Profile preview" class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg">
                    </template>
                    <template x-if="! preview">
                        <x-user-avatar size="lg" class="ring-4 ring-white shadow-lg" />
                    </template>
                    <div class="absolute -bottom-1 -right-1 flex h-8 w-8 items-center justify-center rounded-full bg-primary text-white shadow-md">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                    </div>
                </div>

                <div class="flex-1 space-y-3 text-center sm:text-left">
                    <input
                        type="file"
                        name="profile_picture"
                        accept="image/jpeg,image/png,image/webp"
                        class="form-input text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700"
                        @change="onFileChange($event)"
                    >
                    <p class="text-xs text-gray-500">JPG, PNG, or WebP. Max 2MB.</p>
                    @if (auth()->user()->profile_picture)
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="remove_profile_picture"
                                value="1"
                                class="rounded border-gray-300 text-primary focus:ring-primary/30"
                                x-model="removePicture"
                                @change="if (removePicture) preview = null"
                            >
                            Remove current photo
                        </label>
                    @endif
                    @error('profile_picture')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="reveal-on-scroll reveal-up border-t border-gray-100 pt-6">
            <h4 class="mb-4 text-sm font-semibold text-gray-900">Change Password</h4>
            <div class="space-y-4">
                <div>
                    <x-form.input
                        label="Current Password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        showPasswordToggle
                    />
                </div>
                <div>
                    <x-form.input
                        label="New Password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        hint="Use at least 8 characters with a letter, number, and special character."
                        showPasswordToggle
                    />
                </div>
                <div>
                    <x-form.input
                        label="Confirm New Password"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        showPasswordToggle
                    />
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
            <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
            <button type="submit" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</form>
