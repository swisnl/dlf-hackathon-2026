<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\Submission;
use App\Models\Topic;
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
        $correctCode = <<<'PHP'
            class Post extends Model
            {
                public function comments(): HasMany
                {
                    return $this->hasMany(Comment::class);
                }
            }
            PHP;

        $wrongCode = <<<'PHP'
            class Post extends Model
            {
                public function comments(): HasMany
                {
                    return $this->belongsTo(Comment::class);
                }
            }
            PHP;

        /** @var list<string> $words */
        $words = fake()->words(3);

        return [
            'topic_id' => Topic::factory(),
            'title' => ucfirst(implode(' ', $words)),
            'stars' => fake()->numberBetween(1, 3),
            'correct_code' => $correctCode,
            'wrong_code' => $wrongCode,
            'published_at' => now(),
        ];
    }

    /**
     * Indicate that the exercise is not published yet.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the AI made the exercise about a student's own code.
     */
    public function forSubmission(?Submission $submission = null): static
    {
        return $this->state(fn (array $attributes) => [
            'submission_id' => $submission ?? Submission::factory(),
        ]);
    }
}
