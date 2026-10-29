<?php

namespace Database\Factories;

use App\Enums\ExerciseMessageRole;
use App\Models\Exercise;
use App\Models\ExerciseMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExerciseMessage>
 */
class ExerciseMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exercise_id' => Exercise::factory()->answered(),
            'role' => ExerciseMessageRole::Student,
            'content' => fake()->sentence(),
        ];
    }
}
