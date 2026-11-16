<?php

namespace Database\Factories;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementType;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(2, true),
            'type' => fake()->randomElement(AnnouncementType::cases()),
            'posted_by' => User::factory(),
            'status' => AnnouncementStatus::Active,
            'publish_date' => now()->toDateString(),
            'expiry_date' => now()->addMonth()->toDateString(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AnnouncementStatus::Inactive,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'publish_date' => now()->subMonths(2)->toDateString(),
            'expiry_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'publish_date' => now()->addWeek()->toDateString(),
            'expiry_date' => now()->addMonths(2)->toDateString(),
        ]);
    }
}
