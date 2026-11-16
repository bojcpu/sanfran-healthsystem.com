<?php

namespace App\Services;

use App\Models\Patient;

class PatientCodeService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $lastPatient = Patient::withTrashed()
            ->where('patient_code', 'like', "SF-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = $lastPatient
            ? ((int) substr($lastPatient->patient_code, -4)) + 1
            : 1;

        return sprintf('SF-%s-%04d', $year, $sequence);
    }
}
