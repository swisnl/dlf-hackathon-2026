<?php

namespace Database\Factories;

use App\Enums\AttemptMessageRole;
use App\Models\Attempt;
use App\Models\AttemptMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttemptMessage>
 */
class AttemptMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attempt_id' => Attempt::factory(),
            'role' => AttemptMessageRole::Student,
            'content' => fake()->sentence(),
        ];
    }
}
