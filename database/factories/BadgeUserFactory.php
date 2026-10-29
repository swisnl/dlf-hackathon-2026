<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\BadgeUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BadgeUser>
 */
class BadgeUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'badge_id' => Badge::factory(),
            'user_id' => User::factory(),
            'awarded_at' => now(),
            'seen_at' => null,
        ];
    }

    /**
     * Indicate that the student has seen the badge.
     */
    public function seen(): static
    {
        return $this->state(fn (array $attributes) => [
            'seen_at' => now(),
        ]);
    }
}
