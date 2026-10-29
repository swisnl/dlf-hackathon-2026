<?php

namespace Database\Factories;

use App\Enums\CodeSide;
use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $found = fake()->numberBetween(0, 3);

        return [
            'user_id' => User::factory(),
            'exercise_id' => Exercise::factory(),
            'number' => 1,
            'chosen_side' => CodeSide::Wrong,
            'selected_lines' => fake()->randomElements(range(1, 10), $found),
            'found' => $found,
            'wrong' => fake()->numberBetween(0, 2),
            'stars' => fake()->numberBetween(0, 3),
            'gave_up' => false,
        ];
    }

    /**
     * Indicate that this is the second attempt (retry).
     */
    public function retry(): static
    {
        return $this->state(fn (array $attributes) => [
            'number' => 2,
        ]);
    }

    /**
     * Indicate that the student gave up.
     */
    public function gaveUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'gave_up' => true,
        ]);
    }
}
