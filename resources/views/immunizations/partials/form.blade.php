@php
    $editingImmunization = $editingImmunization ?? null;
    $formModalName = $modalName ?? 'new-immunization';
    $immunizationFormId = $editingImmunization?->id ?? 'new';
@endphp

<form method="POST" action="{{ $editingImmunization ? route('immunizations.update', $editingImmunization) : route('immunizations.store') }}" class="space-y-5">
    @csrf
    @if ($editingImmunization)
        @method('PUT')
        <input type="hidden" name="_return_to" value="{{ $returnTo ?? 'index' }}">
    @endif
    <input type="hidden" name="_modal" value="{{ $formModalName }}">

    <x-form.select
        label="Patient"
        name="patient_id"
        :options="($patients ?? collect())->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
        :value="$editingImmunization?->patient_id ?? request('patient_id')"
        id="immunization-patient-{{ $immunizationFormId }}"
        placeholder="Select patient"
        required
    />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.input label="Vaccine Name" name="vaccine_name" :value="$editingImmunization?->vaccine_name" id="immunization-vaccine-{{ $immunizationFormId }}" required />
        <x-form.input label="Dose" name="dose" :value="$editingImmunization?->dose" id="immunization-dose-{{ $immunizationFormId }}" placeholder="e.g. 1st dose" />
        <x-form.input label="Vaccination Date" name="vaccination_date" type="date" :value="$editingImmunization?->vaccination_date?->format('Y-m-d') ?? now()->format('Y-m-d')" id="immunization-date-{{ $immunizationFormId }}" required />
        <x-form.input label="Next Schedule" name="next_schedule" type="date" :value="$editingImmunization?->next_schedule?->format('Y-m-d')" id="immunization-next-schedule-{{ $immunizationFormId }}" />
    </div>

    <x-form.textarea label="Remarks" name="remarks" :value="$editingImmunization?->remarks" id="immunization-remarks-{{ $immunizationFormId }}" rows="3" />

    <div class="flex gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="btn-primary">{{ $editingImmunization ? 'Update Record' : 'Save Record' }}</button>
        <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
    </div>
</form>
