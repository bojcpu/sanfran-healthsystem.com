@extends('layouts.app')

@section('content')
    <x-page-header title="New Checkup" description="Record a patient consultation.">
        <x-slot:actions>
            <a href="{{ route('checkups.index') }}" class="btn-secondary">Back to Checkups</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('checkups.store') }}" class="space-y-5">
            @csrf

            <x-form.select
                label="Patient"
                name="patient_id"
                :options="($patients ?? collect())->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
                :value="old('patient_id', request('patient_id'))"
                placeholder="Select patient"
                required
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Visit Date" name="visit_date" type="date" :value="old('visit_date', now()->format('Y-m-d'))" required />
                <x-form.input label="Follow-up Date" name="follow_up_date" type="date" />
                <x-form.input label="Attending Personnel" name="attending_personnel" class="sm:col-span-2" />
            </div>

            <x-form.textarea label="Chief Complaint" name="complaint" rows="3" />
            <x-form.textarea label="Diagnosis" name="diagnosis" rows="3" />
            <x-form.textarea label="Treatment" name="treatment" rows="3" />
            <x-form.textarea label="Prescription" name="prescription" rows="3" />
            <x-form.textarea label="Notes" name="notes" rows="3" />

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Save Checkup</button>
                <a href="{{ route('checkups.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
