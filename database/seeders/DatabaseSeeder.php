<?php

namespace Database\Seeders;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\CheckupRecord;
use App\Models\ImmunizationRecord;
use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'System Admin',
            'username' => 'admin',
            'email' => 'admin@sanfran-healthsys.test',
            'password' => Hash::make('password'),
            'role' => UserRole::SystemAdmin,
            'is_active' => true,
        ]);

        $midwife = User::query()->create([
            'name' => 'Maria Santos',
            'username' => 'midwife',
            'email' => 'midwife@sanfran-healthsys.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Midwife,
            'is_active' => true,
        ]);

        $bhw = User::query()->create([
            'name' => 'Juan Dela Cruz',
            'username' => 'bhw',
            'email' => 'bhw@sanfran-healthsys.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Bhw,
            'is_active' => true,
        ]);

        Announcement::query()->create([
            'title' => 'Dengue Alert: Clean Up Drive',
            'content' => 'Residents are urged to remove stagnant water around their homes to prevent dengue-carrying mosquitoes. Report suspected cases to the health center immediately.',
            'type' => AnnouncementType::Emergency,
            'posted_by' => $admin->id,
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->subDays(1)->toDateString(),
            'expiry_date' => now()->addMonth()->toDateString(),
        ]);

        Announcement::query()->create([
            'title' => 'Free Immunization Drive',
            'content' => 'Barangay San Francisco Health Center will conduct a free immunization drive. Please bring your child\'s health records.',
            'type' => AnnouncementType::Immunization,
            'posted_by' => $admin->id,
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->toDateString(),
            'expiry_date' => now()->addMonths(2)->toDateString(),
        ]);

        Announcement::query()->create([
            'title' => 'Prenatal Checkup Schedule',
            'content' => 'Pregnant mothers are advised to visit the health center every Wednesday for prenatal checkups.',
            'type' => AnnouncementType::MaternalCare,
            'posted_by' => $midwife->id,
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->subDays(2)->toDateString(),
        ]);

        Announcement::query()->create([
            'title' => 'Barangay Health Assembly',
            'content' => 'Join the quarterly health assembly at the barangay hall this Saturday, 8:00 AM. Topics include nutrition, sanitation, and available health programs.',
            'type' => AnnouncementType::Event,
            'posted_by' => $admin->id,
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->subDays(3)->toDateString(),
            'expiry_date' => now()->addWeeks(3)->toDateString(),
        ]);

        Announcement::query()->create([
            'title' => 'Heat Advisory for Outdoor Work',
            'content' => 'Avoid prolonged outdoor activity between 10:00 AM and 3:00 PM. Drink plenty of water and watch for signs of heat exhaustion.',
            'type' => AnnouncementType::Advisory,
            'posted_by' => $midwife->id,
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->subDays(4)->toDateString(),
            'expiry_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $codeService = new PatientCodeService;

        $patients = [
            [
                'first_name' => 'Ana',
                'middle_name' => 'Reyes',
                'last_name' => 'Garcia',
                'birth_date' => '1995-03-15',
                'gender' => 'Female',
                'civil_status' => 'Married',
                'contact_number' => '09171234567',
                'address' => 'Purok 1, Barangay San Francisco, Talibon, Bohol',
                'blood_type' => 'O+',
            ],
            [
                'first_name' => 'Pedro',
                'middle_name' => 'Luna',
                'last_name' => 'Mendoza',
                'birth_date' => '2022-08-20',
                'gender' => 'Male',
                'civil_status' => 'Single',
                'contact_number' => '09189876543',
                'address' => 'Purok 2, Barangay San Francisco, Talibon, Bohol',
            ],
            [
                'first_name' => 'Rosa',
                'middle_name' => 'Cruz',
                'last_name' => 'Villanueva',
                'birth_date' => '1998-11-05',
                'gender' => 'Female',
                'civil_status' => 'Single',
                'contact_number' => '09175551234',
                'address' => 'Purok 3, Barangay San Francisco, Talibon, Bohol',
                'blood_type' => 'A+',
            ],
        ];

        foreach ($patients as $data) {
            $data['patient_code'] = $codeService->generate();
            $data['age'] = (int) \Carbon\Carbon::parse($data['birth_date'])->age;
            $data['registered_by'] = $bhw->id;

            $patient = Patient::query()->create($data);

            CheckupRecord::query()->create([
                'patient_id' => $patient->id,
                'user_id' => $midwife->id,
                'visit_date' => now()->subDays(7),
                'complaint' => 'Fever and cough',
                'diagnosis' => 'Upper respiratory infection',
                'treatment' => 'Rest and hydration',
                'prescription' => 'Paracetamol 500mg every 6 hours',
                'attending_personnel' => $midwife->name,
                'follow_up_date' => now()->addDays(7),
            ]);

            if ($patient->gender === 'Male' && $patient->age < 10) {
                ImmunizationRecord::query()->create([
                    'patient_id' => $patient->id,
                    'user_id' => $bhw->id,
                    'vaccine_name' => 'MMR',
                    'dose' => '1st Dose',
                    'vaccination_date' => now()->subMonth(),
                    'next_schedule' => now()->addDays(14),
                ]);
            }

            if ($patient->gender === 'Female' && $patient->age >= 18) {
                MaternalCareRecord::query()->create([
                    'patient_id' => $patient->id,
                    'user_id' => $midwife->id,
                    'pregnancy_number' => 1,
                    'expected_delivery_date' => now()->addMonths(3),
                    'visit_date' => now()->subDays(3),
                    'weight' => '62 kg',
                    'blood_pressure' => '118/76',
                    'findings' => 'Normal fetal heartbeat',
                    'recommendations' => 'Continue prenatal vitamins',
                ]);
            }
        }
    }
}
