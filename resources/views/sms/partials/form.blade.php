<div x-data="{
    patientNumbers: @js(($patients ?? collect())->mapWithKeys(fn ($patient) => [$patient->id => $patient->contact_number])->all()),
    message: {{ json_encode(old('message', '')) }},
    maxLength: 160,
    get remaining() { return this.maxLength - this.message.length; }
}">
    <form method="POST" action="{{ route('sms.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="_modal" value="send-sms">

        <x-form.select
            label="Patient"
            name="patient_id"
            :options="($patients ?? collect())->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
            :value="old('patient_id')"
            placeholder="Select patient to auto-fill number"
            hint="Selecting a patient will auto-fill the mobile number"
            required
            x-on:change="$refs.mobileNumber.value = patientNumbers[$event.target.value] ?? ''"
        />

        <x-form.input label="Mobile Number" name="mobile_number" :value="old('mobile_number')" placeholder="09XXXXXXXXX" required x-ref="mobileNumber" />

        <x-form.select
            label="Notification Type"
            name="notification_type"
            :options="collect(\App\Enums\SmsNotificationType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all()"
            :value="old('notification_type')"
            placeholder="Select type"
            required
        />

        <div>
            <label for="message" class="form-label">Message <span class="text-red-500">*</span></label>
            <textarea
                name="message"
                id="message"
                rows="5"
                required
                x-model="message"
                maxlength="160"
                class="form-textarea @error('message') border-red-500 @enderror"
                placeholder="Enter your SMS message..."
            >{{ old('message') }}</textarea>
            <div class="mt-1 flex justify-between text-xs">
                @error('message')
                    <p class="text-red-600">{{ $message }}</p>
                @else
                    <span></span>
                @enderror
                <span :class="remaining < 20 ? 'text-amber-600' : 'text-gray-500'" x-text="remaining + ' characters remaining'"></span>
            </div>
        </div>

        <div class="flex gap-3 border-t border-gray-100 pt-5">
            <button type="submit" class="btn-primary">Send SMS</button>
            <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
        </div>
    </form>
</div>
