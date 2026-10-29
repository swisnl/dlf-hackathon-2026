<?php

namespace App\Models;

use Database\Factories\BadgeUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * A badge awarded to a user (the badge_user pivot).
 */
#[Fillable(
    ['badge_id', 'user_id', 'awarded_at', 'seen_at'],
)]
class BadgeUser extends Pivot
{
    /** @use HasFactory<BadgeUserFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'badge_user';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
            'seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Badge, $this>
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
