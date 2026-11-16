<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaternalCareRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => [
                'required',
                Rule::exists('patients', 'id')->where(fn ($query) => $query->where('gender', 'Female')->whereNull('deleted_at')),
            ],
            'pregnancy_number' => ['required', 'integer', 'min:1'],
            'expected_delivery_date' => ['nullable', 'date'],
            'visit_date' => ['required', 'date'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:visit_date'],
            'last_menstrual_period' => ['nullable', 'date', 'before_or_equal:visit_date'],
            'para' => ['nullable', 'integer', 'min:0', 'max:255'],
            'trimester' => ['nullable', Rule::in(['1st Trimester', '2nd Trimester', '3rd Trimester'])],
            'gestational_weeks' => ['nullable', 'integer', 'min:0', 'max:45'],
            'gestational_days' => ['nullable', 'integer', 'min:0', 'max:6'],
            'spouse_name' => ['nullable', 'string', 'max:255'],
            'living_children' => ['nullable', 'integer', 'min:0', 'max:255'],
            'birth_plan' => ['nullable', 'string', 'max:255'],
            'hospital' => ['nullable', 'string', 'max:255'],
            'rhu' => ['nullable', 'string', 'max:255'],
            'lgc' => ['nullable', 'string', 'max:255'],
            'birth_attendant' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'string', 'max:20'],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'pulse_rate' => ['nullable', 'string', 'max:20'],
            'respiratory_rate' => ['nullable', 'string', 'max:20'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'present_history' => ['nullable', 'array'],
            'present_history.*' => ['string', Rule::in(['epilepsy', 'severe_headache', 'visual_disturbance', 'edema', 'difficulty_breathing', 'other'])],
            'present_history_other' => ['nullable', 'string', 'max:1000'],
            'past_history' => ['nullable', 'array'],
            'past_history.*' => ['string', Rule::in(['hypertension', 'diabetes', 'asthma', 'heart_disease', 'previous_cs', 'other'])],
            'past_history_other' => ['nullable', 'string', 'max:1000'],
            'family_history' => ['nullable', 'array'],
            'family_history.*' => ['string', Rule::in(['hypertension', 'diabetes', 'asthma', 'heart_disease', 'cancer', 'other'])],
            'family_history_other' => ['nullable', 'string', 'max:1000'],
            'past_health_history' => ['nullable', 'array'],
            'past_health_history.*' => ['string', Rule::in(['drug_allergies', 'previous_operations', 'blood_transfusion', 'smoking', 'alcohol_intake', 'other'])],
            'past_health_history_other' => ['nullable', 'string', 'max:1000'],
            'conjunctiva_findings' => ['nullable', 'array'],
            'conjunctiva_findings.*' => ['string', Rule::in(['pale', 'yellowish'])],
            'neck_findings' => ['nullable', 'array'],
            'neck_findings.*' => ['string', Rule::in(['enlarged_thyroid', 'enlarged_lymph_nodes'])],
            'breast_findings' => ['nullable', 'array'],
            'breast_findings.*' => ['string', Rule::in(['mass', 'nipple_discharge', 'skin_changes', 'enlarged_axillary_nodes'])],
            'abdomen_findings' => ['nullable', 'array'],
            'abdomen_findings.*' => ['string', Rule::in(['enlarged_liver', 'tenderness', 'scar'])],
            'vaginal_findings' => ['nullable', 'array'],
            'vaginal_findings.*' => ['string', Rule::in(['bleeding', 'discharge', 'varicosities', 'pitting_edema'])],
            'vaginal_findings_other' => ['nullable', 'string', 'max:1000'],
            'findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
