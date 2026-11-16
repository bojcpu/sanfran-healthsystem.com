@extends('layouts.app')

@section('content')
    <x-page-header title="Edit Checkup" :description="$checkup->patient->full_name">
        <x-slot:actions>
            <a href="{{ route('patients.show', $checkup->patient) }}" class="btn-secondary">View Patient</a>
            <a href="{{ route('checkups.index') }}" class="btn-secondary">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-3xl p-6">
        <form method="POST" action="{{ route('checkups.update', $checkup) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.select
                label="Patient"
                name="patient_id"
                :options="($patients ?? collect([$checkup->patient]))->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
                :value="$checkup->patient_id"
                required
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Visit Date" name="visit_date" type="date" :value="$checkup->visit_date->format('Y-m-d')" required />
                <x-form.input label="Follow-up Date" name="follow_up_date" type="date" :value="$checkup->follow_up_date?->format('Y-m-d')" />
                <x-form.input label="Attending Personnel" name="attending_personnel" :value="$checkup->attending_personnel" class="sm:col-span-2" />
            </div>

            <x-form.textarea label="Chief Complaint" name="complaint" :value="$checkup->complaint" rows="3" />
            <x-form.textarea label="Diagnosis" name="diagnosis" :value="$checkup->diagnosis" rows="3" />
            <x-form.textarea label="Treatment" name="treatment" :value="$checkup->treatment" rows="3" />
            <x-form.textarea label="Prescription" name="prescription" :value="$checkup->prescription" rows="3" />
            <x-form.textarea label="Notes" name="notes" :value="$checkup->notes" rows="3" />

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Update Checkup</button>
                <a href="{{ route('checkups.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
