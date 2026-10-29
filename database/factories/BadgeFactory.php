<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var list<string> $words */
        $words = fake()->words(2);

        return [
            'name' => ucfirst(implode(' ', $words)),
            'threshold' => fake()->numberBetween(5, 100),
            'icon' => 'lucide-award',
            'color' => fake()->randomElement(['primary', 'success', 'warning', 'danger', 'info', 'gray']),
        ];
    }
}
