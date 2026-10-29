<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\ExerciseError;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseError>
 */
class ExerciseErrorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var list<string> $words */
        $words = fake()->words(3);

        return [
            'exercise_id' => Exercise::factory(),
            'line' => fake()->numberBetween(1, 10),
            'title' => ucfirst(implode(' ', $words)),
            'hint' => fake()->sentence(),
            'explanation' => fake()->paragraph(),
        ];
    }
}
