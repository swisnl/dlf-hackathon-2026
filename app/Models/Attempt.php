<?php

namespace App\Models;

use App\Enums\CodeSide;
use Database\Factories\AttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\CodeSide|null $chosen_side
 * @property array<int, int>          $selected_lines
 */
#[Fillable(
    ['user_id', 'exercise_id', 'number', 'chosen_side', 'selected_lines', 'found', 'wrong', 'stars', 'gave_up'],
)]
class Attempt extends Model
{
    /** @use HasFactory<AttemptFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'chosen_side' => CodeSide::class,
            'selected_lines' => 'array',
            'found' => 'integer',
            'wrong' => 'integer',
            'stars' => 'integer',
            'gave_up' => 'boolean',
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
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
