<?php

namespace Database\Factories;

use App\Models\MaternalCareRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaternalCareRecord>
 */
class MaternalCareRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory()->state([
                'gender' => 'Female',
            ]),
            'user_id' => User::factory(),
            'pregnancy_number' => 1,
            'visit_date' => fake()->date(),
            'weight' => '62 kg',
            'blood_pressure' => '118/76',
        ];
    }
}
