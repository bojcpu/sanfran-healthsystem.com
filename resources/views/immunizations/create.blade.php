@extends('layouts.app')

@section('content')
    <x-page-header title="New Immunization" description="Record a vaccination.">
        <x-slot:actions>
            <a href="{{ route('immunizations.index') }}" class="btn-secondary">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-2xl p-6">
        <form method="POST" action="{{ route('immunizations.store') }}" class="space-y-5">
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
                <x-form.input label="Vaccine Name" name="vaccine_name" required />
                <x-form.input label="Dose" name="dose" placeholder="e.g. 1st dose" />
                <x-form.input label="Vaccination Date" name="vaccination_date" type="date" :value="old('vaccination_date', now()->format('Y-m-d'))" required />
                <x-form.input label="Next Schedule" name="next_schedule" type="date" />
            </div>

            <x-form.textarea label="Remarks" name="remarks" rows="3" />

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Save Record</button>
                <a href="{{ route('immunizations.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
