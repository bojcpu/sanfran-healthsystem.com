<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $birthDate = fake()->dateTimeBetween('-60 years', '-1 year');

        return [
            'patient_code' => sprintf('SF-%s-%04d', now()->format('Y'), fake()->unique()->numberBetween(1, 9999)),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => $birthDate->format('Y-m-d'),
            'age' => (int) now()->diffInYears($birthDate),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed']),
            'contact_number' => '09'.fake()->numerify('#########'),
            'address' => 'Purok 1, Barangay San Francisco, Talibon, Bohol',
            'registered_by' => User::factory(),
        ];
    }
}
