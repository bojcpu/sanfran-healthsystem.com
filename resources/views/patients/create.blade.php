@extends('layouts.app')

@section('content')
    <x-page-header title="Register Patient" description="Add a new patient to the health system.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn-secondary">Back to Patients</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card p-6">
        <form method="POST" action="{{ route('patients.store') }}" class="space-y-6">
            @csrf

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Personal Information</h3>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.input label="First Name" name="first_name" required />
                    <x-form.input label="Middle Name" name="middle_name" />
                    <x-form.input label="Last Name" name="last_name" required />
                    <x-form.input label="Suffix" name="suffix" placeholder="Jr., Sr., III" />
                    <x-form.input label="Birth Date" name="birth_date" type="date" required />
                    <x-form.select label="Gender" name="gender" :options="['Male' => 'Male', 'Female' => 'Female']" placeholder="Select gender" required />
                    <x-form.select label="Civil Status" name="civil_status" :options="['Single' => 'Single', 'Married' => 'Married', 'Widowed' => 'Widowed', 'Separated' => 'Separated', 'Divorced' => 'Divorced']" placeholder="Select status" required />
                    <x-form.input label="Blood Type" name="blood_type" placeholder="e.g. O+" />
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Contact Information</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input label="Contact Number" name="contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only required />
                    <x-form.input label="Email" name="email" type="email" />
                    <div class="sm:col-span-2">
                        <x-patient-address />
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Emergency Contact</h3>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-form.input label="Contact Person" name="emergency_contact_person" />
                    <x-form.input label="Relationship" name="emergency_contact_relationship" />
                    <x-form.input label="Contact Number" name="emergency_contact_number" type="tel" inputmode="numeric" minlength="11" pattern="[0-9]{11}" autocomplete="tel" data-digits-only />
                </div>
            </div>

            <div>
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-primary">Medical Information</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.textarea label="Allergies" name="allergies" rows="3" />
                    <x-form.textarea label="Existing Conditions" name="existing_conditions" rows="3" />
                    <div class="sm:col-span-2">
                        <x-form.textarea label="Notes" name="notes" rows="3" />
                    </div>
                </div>
            </div>

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Register Patient</button>
                <a href="{{ route('patients.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
