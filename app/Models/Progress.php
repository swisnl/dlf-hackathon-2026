<?php

namespace App\Models;

use App\Enums\ProgressStatus;
use Database\Factories\ProgressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * A student's progress on a single exercise (the exercise_user pivot).
 *
 * @property \App\Enums\ProgressStatus $status
 */
#[Fillable(
    ['exercise_id', 'user_id', 'status', 'best_stars', 'completed_at'],
)]
class Progress extends Pivot
{
    /** @use HasFactory<ProgressFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'exercise_user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProgressStatus::class,
            'best_stars' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
