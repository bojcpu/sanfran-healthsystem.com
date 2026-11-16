<x-modal name="record-sms" title="Send SMS" description="Review the patient details and edit the message before sending." maxWidth="lg">
    <div
        x-data="{
            patientName: @js(old('patient_name', '')),
            patientId: @js(old('patient_id', '')),
            mobileNumber: @js(old('mobile_number', '')),
            notificationType: @js(old('notification_type', '')),
            message: @js(old('message', '')),
            remaining() { return 160 - this.message.length; }
        }"
        @open-record-sms.window="
            patientName = $event.detail.patientName;
            patientId = $event.detail.patientId;
            mobileNumber = $event.detail.mobileNumber;
            notificationType = $event.detail.notificationType;
            message = $event.detail.message;
            $store.modal.open('record-sms');
        "
    >
        <form method="POST" action="{{ route('sms.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="_modal" value="record-sms">
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <input type="hidden" name="patient_id" x-model="patientId">
            <input type="hidden" name="notification_type" x-model="notificationType">
            <input type="hidden" name="patient_name" x-model="patientName">

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <span class="form-label">Patient</span>
                    <p class="mt-1 text-sm font-medium text-gray-900" x-text="patientName || '—'"></p>
                </div>
                <div>
                    <label for="record-sms-mobile-number" class="form-label">Mobile Number <span class="text-red-500">*</span></label>
                    <input
                        id="record-sms-mobile-number"
                        type="tel"
                        name="mobile_number"
                        x-model="mobileNumber"
                        maxlength="15"
                        required
                        class="form-input"
                        placeholder="09XXXXXXXXX"
                    >
                </div>
            </div>

            <div>
                <label for="record-sms-message" class="form-label">Message <span class="text-red-500">*</span></label>
                <textarea
                    id="record-sms-message"
                    name="message"
                    rows="5"
                    maxlength="160"
                    required
                    x-model="message"
                    class="form-textarea"
                    placeholder="Enter your SMS message..."
                ></textarea>
                <div class="mt-1 text-right text-xs" :class="remaining() < 20 ? 'text-amber-600' : 'text-gray-500'" x-text="remaining() + ' characters remaining'"></div>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Send SMS</button>
                <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
            </div>
        </form>
    </div>
</x-modal>
