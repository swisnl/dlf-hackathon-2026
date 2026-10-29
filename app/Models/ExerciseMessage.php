<?php

namespace App\Models;

use App\Enums\ExerciseMessageRole;
use Database\Factories\ExerciseMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\ExerciseMessageRole $role
 */
#[Fillable(
    ['role', 'content'],
)]
class ExerciseMessage extends Model
{
    /** @use HasFactory<ExerciseMessageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ExerciseMessageRole::class,
        ];
    }

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
