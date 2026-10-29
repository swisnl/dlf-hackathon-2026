<?php

namespace App\Models;

use App\Enums\Variant;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \App\Enums\Variant      $correct_variant
 * @property \App\Enums\Variant|null $chosen_variant
 */
#[Fillable(
    ['concept', 'question', 'variant_a', 'variant_b', 'correct_variant', 'reference_explanation'],
)]
#[Hidden(
    ['correct_variant', 'reference_explanation'],
)]
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'correct_variant' => Variant::class,
            'chosen_variant' => Variant::class,
            'is_correct' => 'boolean',
            'score' => 'integer',
            'answered_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * @return HasMany<ExerciseMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ExerciseMessage::class)->oldest('id');
    }

    public function isAnswered(): bool
    {
        return $this->answered_at !== null;
    }

    public function variant(Variant $variant): string
    {
        return $variant === Variant::A ? $this->variant_a : $this->variant_b;
    }
}
