<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @property \App\Enums\UserRole $role
 * @property int                 $stars
 */
#[Fillable(
    ['name', 'email', 'password', 'role', 'stars'],
)]
#[Hidden(
    ['password', 'remember_token'],
)]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => UserRole::Student,
        'stars' => 0,
    ];

    /**
     * Award the badges for any star threshold the student reached, whichever way the stars changed.
     */
    protected static function booted(): void
    {
        static::created(fn (User $user) => $user->awardBadges());

        // increment() fires "updated" but not "saved".
        static::updated(function (User $user): void {
            if ($user->wasChanged('stars')) {
                $user->awardBadges();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'stars' => 'integer',
        ];
    }

    /**
     * @return HasMany<Submission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * @return HasMany<Attempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * The student's progress across exercises.
     *
     * @return HasMany<Progress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * The exercises this student has progress on.
     *
     * @return BelongsToMany<Exercise, $this, Progress>
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class)
            ->using(Progress::class)
            ->withPivot(['status', 'best_stars', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * The badges this student has earned.
     *
     * @return BelongsToMany<Badge, $this, BadgeUser>
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)
            ->using(BadgeUser::class)
            ->withPivot(['awarded_at', 'seen_at'])
            ->withTimestamps();
    }

    /**
     * Award every badge whose threshold the student's stars reach and that they do not have yet.
     * Badges are never taken away, and inserting with "or ignore" keeps two concurrent awards from clashing.
     */
    public function awardBadges(): void
    {
        $now = now();

        BadgeUser::query()->insertOrIgnore(
            Badge::query()
                ->where('threshold', '<=', $this->stars)
                ->get(['id'])
                ->map(fn (Badge $badge): array => [
                    'badge_id' => $badge->id,
                    'user_id' => $this->getKey(),
                    'awarded_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all(),
        );
    }

    /**
     * The highest badge (by star threshold) this student has earned.
     */
    public function highestBadge(): ?Badge
    {
        return $this->badges()->orderByDesc('threshold')->first();
    }

    /**
     * The initials of the first and last name, e.g. "DB" for "Daan Bakker".
     */
    public function initials(): string
    {
        $words = Str::of($this->name)->squish()->explode(' ');

        $initials = Str::substr((string) $words->first(), 0, 1);

        if ($words->count() > 1) {
            $initials .= Str::substr((string) $words->last(), 0, 1);
        }

        return Str::upper($initials);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role->hasPanelAccess();
    }

    public function canImpersonate(): bool
    {
        return $this->isAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return !$this->isAdmin();
    }
}
