@extends('layouts.app')

@section('content')
    <x-page-header title="Edit Immunization" :description="$immunization->patient->full_name">
        <x-slot:actions>
            <a href="{{ route('immunizations.index') }}" class="btn-secondary">Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card max-w-2xl p-6">
        <form method="POST" action="{{ route('immunizations.update', $immunization) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-form.select
                label="Patient"
                name="patient_id"
                :options="($patients ?? collect([$immunization->patient]))->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
                :value="$immunization->patient_id"
                required
            />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input label="Vaccine Name" name="vaccine_name" :value="$immunization->vaccine_name" required />
                <x-form.input label="Dose" name="dose" :value="$immunization->dose" />
                <x-form.input label="Vaccination Date" name="vaccination_date" type="date" :value="$immunization->vaccination_date->format('Y-m-d')" required />
                <x-form.input label="Next Schedule" name="next_schedule" type="date" :value="$immunization->next_schedule?->format('Y-m-d')" />
            </div>

            <x-form.textarea label="Remarks" name="remarks" :value="$immunization->remarks" rows="3" />

            <div class="flex gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="btn-primary">Update Record</button>
                <a href="{{ route('immunizations.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
