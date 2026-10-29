<?php

namespace Database\Factories;

use App\Enums\ProgressStatus;
use App\Models\Exercise;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Progress>
 */
class ProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory(),
            'user_id' => User::factory(),
            'status' => ProgressStatus::New,
            'best_stars' => 0,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the exercise has been completed.
     */
    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProgressStatus::Done,
            'best_stars' => fake()->numberBetween(1, 3),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the exercise is up for a retry.
     */
    public function retry(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProgressStatus::Retry,
        ]);
    }
}
