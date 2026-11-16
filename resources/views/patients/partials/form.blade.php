@php
    $editingPatient = $editingPatient ?? null;
    $formModalName = $modalName ?? 'register-patient';
    $patientFormId = $editingPatient?->id ?? 'new';
@endphp

<form method="POST" action="{{ $editingPatient ? route('patients.update', $editingPatient) : route('patients.store') }}" class="space-y-6">
    @csrf
    @if ($editingPatient)
        @method('PUT')
        <input type="hidden" name="_return_to" value="{{ $returnTo ?? 'profile' }}">
    @endif
    <input type="hidden" name="_modal" value="{{ $formModalName }}">

    <div>
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Personal Information</h3>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-form.input label="First Name" name="first_name" :value="$editingPatient?->first_name" id="patient-first-name-{{ $patientFormId }}" required />
            <x-form.input label="Middle Name" name="middle_name" :value="$editingPatient?->middle_name" id="patient-middle-name-{{ $patientFormId }}" />
            <x-form.input label="Last Name" name="last_name" :value="$editingPatient?->last_name" id="patient-last-name-{{ $patientFormId }}" required />
            <x-form.input label="Suffix" name="suffix" :value="$editingPatient?->suffix" id="patient-suffix-{{ $patientFormId }}" placeholder="Jr., Sr., III" />
            <x-form.input label="Birth Date" name="birth_date" type="date" :value="$editingPatient?->birth_date?->format('Y-m-d')" id="patient-birth-date-{{ $patientFormId }}" required />
            <x-form.select label="Gender" name="gender" :options="['Male' => 'Male', 'Female' => 'Female']" :value="$editingPatient?->gender" id="patient-gender-{{ $patientFormId }}" placeholder="Select gender" required />
            <x-form.select label="Civil Status" name="civil_status" :options="['Single' => 'Single', 'Married' => 'Married', 'Widowed' => 'Widowed', 'Separated' => 'Separated', 'Divorced' => 'Divorced']" :value="$editingPatient?->civil_status" id="patient-civil-status-{{ $patientFormId }}" placeholder="Select status" required />
            <x-form.input label="Blood Type" name="blood_type" :value="$editingPatient?->blood_type" id="patient-blood-type-{{ $patientFormId }}" placeholder="e.g. O+" />
        </div>
    </div>

    <div>
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Contact Information</h3>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.input label="Contact Number" name="contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only :value="$editingPatient?->contact_number" id="patient-contact-number-{{ $patientFormId }}" required />
            <x-form.input label="Email" name="email" type="email" :value="$editingPatient?->email" id="patient-email-{{ $patientFormId }}" />
            <div class="sm:col-span-2">
                <x-patient-address :value="$editingPatient?->address" id="patient-address-{{ $patientFormId }}" />
            </div>
        </div>
    </div>

    <div>
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Emergency Contact</h3>
        <div class="grid gap-5 sm:grid-cols-3">
            <x-form.input label="Contact Person" name="emergency_contact_person" :value="$editingPatient?->emergency_contact_person" id="patient-emergency-person-{{ $patientFormId }}" />
            <x-form.input label="Relationship" name="emergency_contact_relationship" :value="$editingPatient?->emergency_contact_relationship" id="patient-emergency-relationship-{{ $patientFormId }}" />
            <x-form.input label="Contact Number" name="emergency_contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only :value="$editingPatient?->emergency_contact_number" id="patient-emergency-number-{{ $patientFormId }}" />
        </div>
    </div>

    <div>
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Medical Information</h3>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-form.textarea label="Allergies" name="allergies" :value="$editingPatient?->allergies" id="patient-allergies-{{ $patientFormId }}" rows="3" />
            <x-form.textarea label="Existing Conditions" name="existing_conditions" :value="$editingPatient?->existing_conditions" id="patient-conditions-{{ $patientFormId }}" rows="3" />
            <div class="sm:col-span-2">
                <x-form.textarea label="Notes" name="notes" :value="$editingPatient?->notes" id="patient-notes-{{ $patientFormId }}" rows="3" />
            </div>
        </div>
    </div>

    <div class="flex gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="btn-primary">{{ $editingPatient ? 'Update Patient' : 'Register Patient' }}</button>
        <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
    </div>
</form>
