<?php

namespace Database\Factories;

use App\Models\CheckupRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CheckupRecord>
 */
class CheckupRecordFactory extends Factory
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
            'visit_date' => fake()->date(),
            'complaint' => 'Fever and cough',
            'diagnosis' => 'Upper respiratory infection',
            'treatment' => 'Rest and hydration',
            'attending_personnel' => fake()->name(),
        ];
    }
}
