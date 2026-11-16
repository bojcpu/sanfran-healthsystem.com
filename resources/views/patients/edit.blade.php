@extends('layouts.app')

@section('content')
    <x-page-header :title="'Edit Patient'" :description="$patient->full_name . ' (' . $patient->patient_code . ')'">
        <x-slot:actions>
            <a href="{{ route('patients.show', $patient) }}" class="btn-secondary">View Profile</a>
            <a href="{{ route('patients.index') }}" class="btn-secondary">Back to Patients</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card p-6">
        <form method="POST" action="{{ route('patients.update', $patient) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Personal Information</h3>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.input label="First Name" name="first_name" :value="$patient->first_name" required />
                    <x-form.input label="Middle Name" name="middle_name" :value="$patient->middle_name" />
                    <x-form.input label="Last Name" name="last_name" :value="$patient->last_name" required />
                    <x-form.input label="Suffix" name="suffix" :value="$patient->suffix" />
                    <x-form.input label="Birth Date" name="birth_date" type="date" :value="$patient->birth_date->format('Y-m-d')" required />
                    <x-form.select label="Gender" name="gender" :options="['Male' => 'Male', 'Female' => 'Female']" :value="$patient->gender" required />
                    <x-form.select label="Civil Status" name="civil_status" :options="['Single' => 'Single', 'Married' => 'Married', 'Widowed' => 'Widowed', 'Separated' => 'Separated', 'Divorced' => 'Divorced']" :value="$patient->civil_status" required />
                    <x-form.input label="Blood Type" name="blood_type" :value="$patient->blood_type" />
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Contact Information</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input label="Contact Number" name="contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only :value="$patient->contact_number" required />
                    <x-form.input label="Email" name="email" type="email" :value="$patient->email" />
                    <div class="sm:col-span-2">
                        <x-patient-address :value="$patient->address" id="patient-address-edit" />
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Emergency Contact</h3>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-form.input label="Contact Person" name="emergency_contact_person" :value="$patient->emergency_contact_person" />
                    <x-form.input label="Relationship" name="emergency_contact_relationship" :value="$patient->emergency_contact_relationship" />
                    <x-form.input label="Contact Number" name="emergency_contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only :value="$patient->emergency_contact_number" />
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Medical Information</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.textarea label="Allergies" name="allergies" :value="$patient->allergies" rows="3" />
                    <x-form.textarea label="Existing Conditions" name="existing_conditions" :value="$patient->existing_conditions" rows="3" />
                    <div class="sm:col-span-2">
                        <x-form.textarea label="Notes" name="notes" :value="$patient->notes" rows="3" />
                    </div>
                </div>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Update Patient</button>
                <a href="{{ route('patients.show', $patient) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
