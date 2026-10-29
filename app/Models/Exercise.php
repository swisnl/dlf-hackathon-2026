<?php

namespace App\Models;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Enums\Variant;
use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property string                          $correct_code
 * @property string                          $wrong_code
 * @property Topic                           $topic
 */
#[Fillable(
    ['topic_id', 'submission_id', 'title', 'stars', 'correct_code', 'wrong_code', 'published_at'],
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
            'stars' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The student's code the AI made this exercise about, if any.
     *
     * @return BelongsTo<Submission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Which variant shows the correct code. The AI tends to put the correct code first,
     * so the order is derived from the id and app key: stable per exercise, but not guessable.
     */
    public function correctVariant(): Variant
    {
        return ord(hash_hmac('sha256', (string) $this->id, config()->string('app.key'), true)[0]) % 2 === 0 ? Variant::A : Variant::B;
    }

    public function side(Variant $variant): CodeSide
    {
        return $variant === $this->correctVariant() ? CodeSide::Correct : CodeSide::Wrong;
    }

    public function variant(CodeSide $side): Variant
    {
        return $side === CodeSide::Correct ? $this->correctVariant() : $this->correctVariant()->other();
    }

    public function code(Variant $variant): string
    {
        return $this->side($variant) === CodeSide::Correct ? $this->correct_code : $this->wrong_code;
    }

    /**
     * What is wrong with the wrong code, as one text for the AI.
     */
    public function referenceExplanation(): string
    {
        return $this->errors
            ->map(fn (ExerciseError $error): string => "Regel {$error->line} van de foute versie, {$error->title}: {$error->explanation}")
            ->implode("\n");
    }

    /**
     * Only exercises that students can see.
     *
     * @param Builder<$this> $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }

    /**
     * Load only the given student's progress, so progressOfStudent() and statusOfStudent() can use it.
     *
     * @param Builder<$this> $query
     */
    #[Scope]
    protected function withProgressOf(Builder $query, User $user): void
    {
        $query->with(['progress' => fn (HasMany $query) => $query->whereBelongsTo($user)]);
    }

    /**
     * The student's progress, loaded through withProgressOf().
     */
    public function progressOfStudent(): ?Progress
    {
        return $this->progress->first();
    }

    /**
     * The student's status, loaded through withProgressOf(); no progress yet means it is new.
     */
    public function statusOfStudent(): ProgressStatus
    {
        return $this->progressOfStudent()->status ?? ProgressStatus::New;
    }

    /**
     * @return BelongsTo<Topic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * @return HasMany<ExerciseError, $this>
     */
    public function errors(): HasMany
    {
        return $this->hasMany(ExerciseError::class);
    }

    /**
     * The lines of the wrong class that contain an error; every exercise has at least one.
     *
     * @return list<int>
     */
    public function errorLines(): array
    {
        return array_values($this->errors->map(fn (ExerciseError $error): int => $error->line)->all());
    }

    /**
     * @return HasMany<Attempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * @return HasMany<Progress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /**
     * The students who have progress on this exercise.
     *
     * @return BelongsToMany<User, $this, Progress>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(Progress::class)
            ->withPivot(['status', 'best_stars', 'completed_at'])
            ->withTimestamps();
    }
}
