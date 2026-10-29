<?php

namespace Database\Factories;

use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'user_id' => fn (array $attributes) => Submission::query()->whereKey($attributes['submission_id'])->value('user_id'),
            'concept' => 'Private properties',
            'question' => 'Welke variant van de class Cart is juist?',
            'variant_a' => "class Cart\n{\n    private array \$items = [];\n}",
            'variant_b' => "class Cart\n{\n    private array \$items;\n}",
            'correct_variant' => Variant::A,
            'reference_explanation' => 'In variant B is $items niet geïnitialiseerd.',
        ];
    }

    /**
     * Indicate that the student already answered the exercise.
     */
    public function answered(Variant $choice = Variant::A): static
    {
        return $this->state(fn (array $attributes) => [
            'chosen_variant' => $choice,
            'explanation' => 'Omdat de array een beginwaarde heeft.',
            'is_correct' => $choice === ($attributes['correct_variant'] ?? Variant::A),
            'score' => 8,
            'feedback' => 'Goed gedaan.',
            'answered_at' => now(),
        ]);
    }
}
