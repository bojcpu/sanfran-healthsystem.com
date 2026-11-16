@php
    $record = $record ?? null;
    $modalName = $modalName ?? null;
    $maternalFormId = $modalName ?? 'maternal-care-form';
    $initialPatientId = (string) old('patient_id', $record?->patient_id ?? request('patient_id', ''));
    $initialPregnancyNumber = old('pregnancy_number', $record?->pregnancy_number ?? 1);
    $initialPara = old('para', $record?->para ?? 0);
    $initialLmp = old('last_menstrual_period', $record?->last_menstrual_period?->format('Y-m-d') ?? '');
    $initialVisitDate = old('visit_date', $record?->visit_date?->format('Y-m-d') ?? now()->toDateString());
    $initialEdd = old('expected_delivery_date', $record?->expected_delivery_date?->format('Y-m-d') ?? '');
    $initialGestationalWeeks = old('gestational_weeks', $record?->gestational_weeks ?? '');
    $initialGestationalDays = old('gestational_days', $record?->gestational_days ?? '');
    $initialTrimester = old('trimester', $record?->trimester ?? '');
    $patientSummaries = $patients->map(fn ($patient) => [
        'id' => $patient->id,
        'name' => $patient->full_name,
        'code' => $patient->patient_code,
        'birthDate' => $patient->birth_date->format('M d, Y'),
        'age' => $patient->age ?? $patient->computeAge(),
        'contact' => $patient->contact_number,
        'address' => $patient->address,
    ])->values();
    $historyGroups = [
        ['name' => 'present_history', 'title' => 'Present', 'other' => 'present_history_other', 'choices' => ['epilepsy' => 'Epilepsy / Convulsion / Seizure', 'severe_headache' => 'Severe headache / Dizziness', 'visual_disturbance' => 'Visual disturbance / Blurring of vision', 'edema' => 'Edema / Swelling of face and hands', 'difficulty_breathing' => 'Difficulty of breathing', 'other' => 'Others (Specify)']],
        ['name' => 'past_history', 'title' => 'Past', 'other' => 'past_history_other', 'choices' => ['hypertension' => 'Hypertension', 'diabetes' => 'Diabetes mellitus', 'asthma' => 'Asthma', 'heart_disease' => 'Heart disease', 'previous_cs' => 'Previous CS', 'other' => 'Others (Specify)']],
        ['name' => 'family_history', 'title' => 'Family History', 'other' => 'family_history_other', 'choices' => ['hypertension' => 'Hypertension', 'diabetes' => 'Diabetes mellitus', 'asthma' => 'Asthma', 'heart_disease' => 'Heart disease', 'cancer' => 'Cancer', 'other' => 'Others (Specify)']],
        ['name' => 'past_health_history', 'title' => 'Past Health History', 'other' => 'past_health_history_other', 'choices' => ['drug_allergies' => 'Drug allergies', 'previous_operations' => 'Previous operations', 'blood_transfusion' => 'History of blood transfusion', 'smoking' => 'Smoking', 'alcohol_intake' => 'Alcohol intake', 'other' => 'Others (Specify)']],
    ];
    $examGroups = [
        ['name' => 'conjunctiva_findings', 'title' => 'Conjunctiva', 'choices' => ['pale' => 'Pale', 'yellowish' => 'Yellowish']],
        ['name' => 'neck_findings', 'title' => 'Neck', 'choices' => ['enlarged_thyroid' => 'Enlarged thyroid', 'enlarged_lymph_nodes' => 'Enlarged lymph nodes']],
        ['name' => 'breast_findings', 'title' => 'Breast', 'choices' => ['mass' => 'Mass', 'nipple_discharge' => 'Nipple discharge', 'skin_changes' => 'Skin changes (peel or dimpling)', 'enlarged_axillary_nodes' => 'Enlarged axillary lymph nodes']],
        ['name' => 'abdomen_findings', 'title' => 'Abdomen', 'choices' => ['enlarged_liver' => 'Enlarged liver', 'tenderness' => 'Tenderness', 'scar' => 'Scar']],
    ];
    $sectionHeader = 'flex items-center gap-2 border-b border-emerald-100 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-950';
    $fieldClass = 'form-input py-2';
@endphp

<form
    id="{{ $maternalFormId }}"
    method="POST"
    action="{{ $record ? route('maternal-care.update', $record) : route('maternal-care.store') }}"
    x-data="{
        selectedPatientId: @js($initialPatientId),
        patients: @js($patientSummaries),
        gravida: @js((int) $initialPregnancyNumber),
        para: @js((int) $initialPara),
        lmp: @js($initialLmp),
        visitDate: @js($initialVisitDate),
        gestationalWeeks: @js($initialGestationalWeeks),
        gestationalDays: @js($initialGestationalDays),
        trimester: @js($initialTrimester),
        expectedDeliveryDate: @js($initialEdd),
        aogManuallyChanged: false,
        trimesterManuallyChanged: false,
        eddManuallyChanged: false,
        get selectedPatient() { return this.patients.find(patient => String(patient.id) === String(this.selectedPatientId)) || null },
        get gestationalAge() {
            if (this.gestationalWeeks === '' || this.gestationalWeeks === null) return '—';
            return `${this.gestationalWeeks} weeks ${this.gestationalDays || 0} days`;
        },
        init() {
            if ((this.gestationalWeeks === '' || this.gestationalWeeks === null) && this.lmp && this.visitDate) this.updateAogFromDates();
            if (!this.expectedDeliveryDate && this.lmp) this.updateEddFromLmp();
        },
        updateAogFromDates() {
            if (!this.lmp || !this.visitDate) return;
            const lmpDate = new Date(`${this.lmp}T00:00:00Z`);
            const visitDate = new Date(`${this.visitDate}T00:00:00Z`);
            const days = Math.floor((visitDate - lmpDate) / 86400000);
            if (days < 0) return;
            if (!this.aogManuallyChanged) {
                this.gestationalWeeks = Math.floor(days / 7);
                this.gestationalDays = days % 7;
            }
            this.updateTrimesterFromAog();
        },
        updateTrimesterFromAog() {
            if (this.trimesterManuallyChanged || this.gestationalWeeks === '' || this.gestationalWeeks === null) return;
            if (this.gestationalWeeks < 14) this.trimester = '1st Trimester';
            else if (this.gestationalWeeks < 28) this.trimester = '2nd Trimester';
            else this.trimester = '3rd Trimester';
        },
        updateEddFromLmp() {
            if (!this.lmp || this.eddManuallyChanged) return;
            const [year, month, day] = this.lmp.split('-').map(Number);
            const due = new Date(Date.UTC(year, month - 1, day + 280));
            this.expectedDeliveryDate = due.toISOString().slice(0, 10);
        },
        formatDate(value) {
            if (!value) return '—';
            return new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC' }).format(new Date(`${value}T00:00:00Z`));
        }
    }"
    class="space-y-4"
>
    @csrf
    @if ($record)
        @method('PUT')
        <input type="hidden" name="_return_to" value="{{ $returnTo ?? 'index' }}">
    @endif
    @if ($modalName)
        <input type="hidden" name="_modal" value="{{ $modalName }}">
    @endif

    <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-12">
        <div class="space-y-3 lg:col-span-7">
            <label class="block text-sm font-medium text-gray-700">Select Patient
                <select name="patient_id" x-model="selectedPatientId" class="form-select mt-1" required>
                    <option value="">Select a female patient</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->patient_code }} — {{ $patient->full_name }}</option>
                    @endforeach
                </select>
            </label>
            @error('patient_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <div class="flex min-w-0 items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary-50 text-2xl font-bold text-primary" x-text="selectedPatient?.name?.charAt(0)?.toUpperCase() || '?'">?</div>
                <div class="min-w-0">
                    <p class="truncate text-base font-semibold text-gray-900" x-text="selectedPatient?.name || 'Patient profile will appear here'">Patient profile will appear here</p>
                    <p class="text-sm text-gray-600"><span x-text="selectedPatient?.code || 'Patient code'">Patient code</span><span x-show="selectedPatient"> · <span x-text="selectedPatient?.age"></span> years old</span></p>
                    <p class="text-sm text-gray-600"><span x-text="selectedPatient?.birthDate || 'Date of birth'">Date of birth</span><span x-show="selectedPatient?.contact"> · <span x-text="selectedPatient?.contact"></span></span></p>
                    <p class="truncate text-sm text-gray-600" x-text="selectedPatient?.address || 'Address'">Address</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 rounded-lg bg-emerald-50 p-3 text-sm lg:col-span-5">
            <label class="text-xs font-medium text-gray-600">Gravida
                <input type="number" name="pregnancy_number" min="1" x-model.number="gravida" value="{{ $initialPregnancyNumber }}" class="{{ $fieldClass }} mt-1" required>
            </label>
            <label class="text-xs font-medium text-gray-600">Para
                <input type="number" name="para" min="0" x-model.number="para" value="{{ $initialPara }}" class="{{ $fieldClass }} mt-1">
            </label>
            <label class="text-xs font-medium text-gray-600">Current Trimester
                <select name="trimester" x-model="trimester" @change="trimesterManuallyChanged = true" class="form-select mt-1">
                    <option value="">Select trimester</option>
                    <option value="1st Trimester">1st Trimester</option>
                    <option value="2nd Trimester">2nd Trimester</option>
                    <option value="3rd Trimester">3rd Trimester</option>
                </select>
            </label>
            <div class="text-xs font-medium text-gray-600">AOG
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <label class="sr-only" for="{{ $maternalFormId }}-gestational-weeks">Weeks</label>
                    <input id="{{ $maternalFormId }}-gestational-weeks" type="number" name="gestational_weeks" min="0" max="45" x-model.number="gestationalWeeks" @input="aogManuallyChanged = true; updateTrimesterFromAog()" class="{{ $fieldClass }}" placeholder="Weeks" aria-label="AOG weeks">
                    <label class="sr-only" for="{{ $maternalFormId }}-gestational-days">Days</label>
                    <input id="{{ $maternalFormId }}-gestational-days" type="number" name="gestational_days" min="0" max="6" x-model.number="gestationalDays" @input="aogManuallyChanged = true; updateTrimesterFromAog()" class="{{ $fieldClass }}" placeholder="Days" aria-label="AOG days">
                </div>
            </div>
            <label class="text-xs font-medium text-gray-600">Expected Delivery Date
                <input type="date" name="expected_delivery_date" x-model="expectedDeliveryDate" @input="eddManuallyChanged = true" class="{{ $fieldClass }} mt-1">
            </label>
            <label class="text-xs font-medium text-gray-600">Visit Date
                <input type="date" name="visit_date" x-model="visitDate" @change="updateAogFromDates()" value="{{ $initialVisitDate }}" class="{{ $fieldClass }} mt-1" required>
            </label>
            @error('visit_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            <label class="text-xs font-medium text-gray-600">Prenatal Follow-up Date
                <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $record?->follow_up_date?->format('Y-m-d')) }}" min="{{ $initialVisitDate }}" class="{{ $fieldClass }} mt-1">
            </label>
            @error('follow_up_date') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid gap-3 xl:grid-cols-12">
        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-3">
            <h2 class="{{ $sectionHeader }}">Medical History</h2>
            <div class="space-y-4 p-4">
                @foreach (array_slice($historyGroups, 0, 2) as $group)
                    <fieldset>
                        <legend class="mb-2 text-xs font-bold uppercase text-gray-800">{{ $group['title'] }}</legend>
                        <div class="space-y-1.5">
                            @foreach ($group['choices'] as $value => $label)
                                <label class="flex items-start gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="{{ $group['name'] }}[]" value="{{ $value }}" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary" @checked(in_array($value, old($group['name'], $record?->{$group['name']} ?? []), true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <input type="text" name="{{ $group['other'] }}" value="{{ old($group['other'], $record?->{$group['other']}) }}" class="{{ $fieldClass }} mt-2" placeholder="Specify other history">
                    </fieldset>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-5">
            <h2 class="{{ $sectionHeader }}">Physical Examination</h2>
            <div class="space-y-5 p-4">
                <fieldset>
                    <legend class="mb-2 text-xs font-bold uppercase text-gray-800">Vital Signs</legend>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <label class="text-xs text-gray-600">Blood Pressure (mmHg)<input name="blood_pressure" value="{{ old('blood_pressure', $record?->blood_pressure) }}" class="{{ $fieldClass }} mt-1" placeholder="120/80"></label>
                        <label class="text-xs text-gray-600">Pulse Rate (bpm)<input name="pulse_rate" value="{{ old('pulse_rate', $record?->pulse_rate) }}" class="{{ $fieldClass }} mt-1" inputmode="numeric"></label>
                        <label class="text-xs text-gray-600">Respiration Rate (/min)<input name="respiratory_rate" value="{{ old('respiratory_rate', $record?->respiratory_rate) }}" class="{{ $fieldClass }} mt-1" inputmode="numeric"></label>
                        <label class="text-xs text-gray-600">Temperature (°C)<input name="temperature" value="{{ old('temperature', $record?->temperature) }}" class="{{ $fieldClass }} mt-1" type="number" step="0.1" min="0" max="60"></label>
                        <label class="text-xs text-gray-600">Weight (kg)<input name="weight" value="{{ old('weight', $record?->weight) }}" class="{{ $fieldClass }} mt-1" placeholder="55"></label>
                        <label class="text-xs text-gray-600">Height (cm)<input name="height" value="{{ old('height', $record?->height) }}" class="{{ $fieldClass }} mt-1" type="number" step="0.1" min="0" max="300"></label>
                    </div>
                </fieldset>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($examGroups as $group)
                        <fieldset>
                            <legend class="mb-2 text-xs font-bold uppercase text-gray-800">{{ $group['title'] }}</legend>
                            <div class="space-y-1.5">
                                @foreach ($group['choices'] as $value => $label)
                                    <label class="flex items-start gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="{{ $group['name'] }}[]" value="{{ $value }}" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary" @checked(in_array($value, old($group['name'], $record?->{$group['name']} ?? []), true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-4">
            <h2 class="{{ $sectionHeader }}">Client Information</h2>
            <div class="space-y-3 p-4">
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="text-xs text-gray-600">Family Serial No.<input :value="selectedPatient?.code || ''" class="{{ $fieldClass }} mt-1" readonly></label>
                    <label class="text-xs text-gray-600">Name of Client<input :value="selectedPatient?.name || ''" class="{{ $fieldClass }} mt-1" readonly></label>
                    <label class="text-xs text-gray-600">Name of Spouse<input name="spouse_name" value="{{ old('spouse_name', $record?->spouse_name) }}" class="{{ $fieldClass }} mt-1"></label>
                    <label class="text-xs text-gray-600">No. of Living Children<input name="living_children" value="{{ old('living_children', $record?->living_children) }}" class="{{ $fieldClass }} mt-1" type="number" min="0"></label>
                    <label class="text-xs text-gray-600">Birth Plan<input name="birth_plan" value="{{ old('birth_plan', $record?->birth_plan) }}" class="{{ $fieldClass }} mt-1"></label>
                    <label class="text-xs text-gray-600">Hospital<input name="hospital" value="{{ old('hospital', $record?->hospital) }}" class="{{ $fieldClass }} mt-1"></label>
                    <label class="text-xs text-gray-600">RHU<input name="rhu" value="{{ old('rhu', $record?->rhu) }}" class="{{ $fieldClass }} mt-1"></label>
                    <label class="text-xs text-gray-600">LGC<input name="lgc" value="{{ old('lgc', $record?->lgc) }}" class="{{ $fieldClass }} mt-1"></label>
                    <label class="text-xs text-gray-600">Birth Attendant<input name="birth_attendant" value="{{ old('birth_attendant', $record?->birth_attendant) }}" class="{{ $fieldClass }} mt-1"></label>
                </div>
            </div>
        </section>

        @foreach (array_slice($historyGroups, 2) as $group)
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-3">
                <h2 class="{{ $sectionHeader }}">{{ $group['title'] }}</h2>
                <div class="space-y-1.5 p-4">
                    @foreach ($group['choices'] as $value => $label)
                        <label class="flex items-start gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="{{ $group['name'] }}[]" value="{{ $value }}" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-primary" @checked(in_array($value, old($group['name'], $record?->{$group['name']} ?? []), true))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    <input type="text" name="{{ $group['other'] }}" value="{{ old($group['other'], $record?->{$group['other']}) }}" class="{{ $fieldClass }} mt-2" placeholder="Specify other history">
                </div>
            </section>
        @endforeach

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-3">
            <h2 class="{{ $sectionHeader }}">Vaginal Examination</h2>
            <div class="space-y-2.5 p-4">
                @foreach (['bleeding' => 'With bleeding', 'discharge' => 'With discharge', 'varicosities' => 'Varicosities', 'pitting_edema' => 'Pitting edema'] as $value => $label)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="vaginal_findings[]" value="{{ $value }}" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(in_array($value, old('vaginal_findings', $record?->vaginal_findings ?? []), true))>
                        {{ $label }}
                    </label>
                @endforeach
                <input name="vaginal_findings_other" value="{{ old('vaginal_findings_other', $record?->vaginal_findings_other) }}" class="{{ $fieldClass }}" placeholder="Other findings">
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-3">
            <h2 class="{{ $sectionHeader }}">Menstrual / Pregnancy Information</h2>
            <div class="space-y-3 p-4">
                <label class="block text-xs text-gray-600">Last Menstrual Period (LMP)
                    <input type="date" name="last_menstrual_period" x-model="lmp" @change="updateAogFromDates(); updateEddFromLmp()" :max="visitDate" class="{{ $fieldClass }} mt-1">
                </label>
                @error('last_menstrual_period') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500">AOG and trimester are suggested from LMP and visit date. You can adjust them above.</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm xl:col-span-3">
            <h2 class="{{ $sectionHeader }}">Assessment</h2>
            <div class="space-y-3 p-4">
                <label class="block text-xs text-gray-600">Impression / Diagnosis
                    <textarea name="findings" rows="3" class="form-textarea mt-1">{{ old('findings', $record?->findings) }}</textarea>
                </label>
                <label class="block text-xs text-gray-600">Recommendations
                    <textarea name="recommendations" rows="2" class="form-textarea mt-1">{{ old('recommendations', $record?->recommendations) }}</textarea>
                </label>
                <label class="block text-xs text-gray-600">Remarks
                    <textarea name="remarks" rows="2" class="form-textarea mt-1">{{ old('remarks', $record?->remarks) }}</textarea>
                </label>
            </div>
        </section>
    </div>

    <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
        @if ($modalName)
            <button type="button" class="btn-secondary" @click="$store.modal.close()">Cancel</button>
        @else
            <a href="{{ route('maternal-care.index') }}" class="btn-secondary">Cancel</a>
        @endif
        <button type="submit" class="btn-primary">{{ $record ? 'Save Changes' : 'Save Record' }}</button>
    </div>
</form>
