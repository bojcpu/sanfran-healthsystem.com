@php
    $editingCheckup = $editingCheckup ?? null;
    $formModalName = $modalName ?? 'new-checkup';
    $checkupFormId = $editingCheckup?->id ?? 'new';
@endphp

<form method="POST" action="{{ $editingCheckup ? route('checkups.update', $editingCheckup) : route('checkups.store') }}" class="space-y-5">
    @csrf
    @if ($editingCheckup)
        @method('PUT')
        <input type="hidden" name="_return_to" value="{{ $returnTo ?? 'index' }}">
    @endif
    <input type="hidden" name="_modal" value="{{ $formModalName }}">

    <x-form.select
        label="Patient"
        name="patient_id"
        :options="($patients ?? collect())->mapWithKeys(fn ($p) => [$p->id => $p->patient_code . ' — ' . $p->full_name])->all()"
        :value="$editingCheckup?->patient_id ?? request('patient_id')"
        id="checkup-patient-{{ $checkupFormId }}"
        placeholder="Select patient"
        required
    />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-form.input label="Visit Date" name="visit_date" type="date" :value="$editingCheckup?->visit_date?->format('Y-m-d') ?? now()->format('Y-m-d')" id="checkup-visit-date-{{ $checkupFormId }}" required />
        <x-form.input label="Follow-up Date" name="follow_up_date" type="date" :value="$editingCheckup?->follow_up_date?->format('Y-m-d')" id="checkup-follow-up-{{ $checkupFormId }}" />
        <x-form.input label="Attending Personnel" name="attending_personnel" :value="$editingCheckup?->attending_personnel" id="checkup-attending-{{ $checkupFormId }}" class="sm:col-span-2" />
    </div>

    <x-form.textarea label="Chief Complaint" name="complaint" :value="$editingCheckup?->complaint" id="checkup-complaint-{{ $checkupFormId }}" rows="3" />
    <x-form.textarea label="Diagnosis" name="diagnosis" :value="$editingCheckup?->diagnosis" id="checkup-diagnosis-{{ $checkupFormId }}" rows="3" />
    <x-form.textarea label="Treatment" name="treatment" :value="$editingCheckup?->treatment" id="checkup-treatment-{{ $checkupFormId }}" rows="3" />
    <x-form.textarea label="Prescription" name="prescription" :value="$editingCheckup?->prescription" id="checkup-prescription-{{ $checkupFormId }}" rows="3" />
    <x-form.textarea label="Notes" name="notes" :value="$editingCheckup?->notes" id="checkup-notes-{{ $checkupFormId }}" rows="3" />

    <div class="flex gap-3 border-t border-gray-100 pt-5">
        <button type="submit" class="btn-primary">{{ $editingCheckup ? 'Update Checkup' : 'Save Checkup' }}</button>
        <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
    </div>
</form>
