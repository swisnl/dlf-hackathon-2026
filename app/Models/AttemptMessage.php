<?php

namespace App\Models;

use App\Enums\AttemptMessageRole;
use Database\Factories\AttemptMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\AttemptMessageRole $role
 */
#[Fillable(
    ['role', 'content'],
)]
class AttemptMessage extends Model
{
    /** @use HasFactory<AttemptMessageFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AttemptMessageRole::class,
        ];
    }

    /**
     * @return BelongsTo<Attempt, $this>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }
}
