<?php

namespace Database\Factories;

use App\Models\ImmunizationRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImmunizationRecord>
 */
class ImmunizationRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'user_id' => User::factory(),
            'vaccine_name' => 'MMR',
            'dose' => '1st Dose',
            'vaccination_date' => fake()->date(),
        ];
    }
}
