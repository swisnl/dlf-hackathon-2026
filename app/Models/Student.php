<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * A user with the student role, so the backoffice can authorize students separately from user management.
 */
class Student extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('student', static fn (Builder $query) => $query->where('role', UserRole::Student));
    }

    /**
     * Keep the relations inherited from User pointing at user_id.
     */
    #[Override]
    public function getForeignKey(): string
    {
        return 'user_id';
    }

    /**
     * Keep the pivot tables inherited from User (badge_user, exercise_user).
     */
    #[Override]
    public function joiningTableSegment(): string
    {
        return 'user';
    }
}
