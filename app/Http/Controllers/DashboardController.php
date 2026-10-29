<?php

namespace App\Http\Controllers;

use App\Enums\ProgressStatus;
use App\Models\Badge;
use App\Models\Exercise;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * How many exercises "Verder oefenen" suggests.
     */
    private const SUGGESTIONS = 3;

    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $badges = Badge::query()->orderBy('threshold')->get();
        $earnedBadgeIds = $user->badges()->pluck('badges.id');

        $exercises = Exercise::query()
            ->published()
            ->with(['topic', 'progress' => fn (HasMany $query) => $query->whereBelongsTo($user)])
            ->orderBy('published_at')
            ->orderBy('id')
            ->get();

        $withStatus = fn (ProgressStatus $status) => $exercises->filter(fn (Exercise $exercise): bool => $this->status($exercise) === $status);
        // The most recently attempted retry first.
        $retries = $withStatus(ProgressStatus::Retry)->sortByDesc(fn (Exercise $exercise) => $exercise->progress->first()?->updated_at);
        $new = $withStatus(ProgressStatus::New);

        return Inertia::render('Dashboard', [
            'badges' => $badges->map(fn (Badge $badge): array => [
                ...$badge->only('id', 'name', 'threshold', 'icon', 'color'),
                'earned' => $earnedBadgeIds->contains($badge->id),
            ]),
            'nextBadge' => $badges->first(fn (Badge $badge): bool => $badge->threshold > $user->stars)?->only('name', 'threshold'),
            'exercisesDone' => $withStatus(ProgressStatus::Done)->count(),
            'exercisesTotal' => $exercises->count(),
            'suggestions' => $retries->concat($new)->take(self::SUGGESTIONS)->values()->map(fn (Exercise $exercise): array => [
                ...$exercise->only('id', 'title', 'stars'),
                'bestStars' => $exercise->progress->first()->best_stars ?? 0,
                'status' => $this->status($exercise)->value,
                'statusLabel' => $this->status($exercise)->getLabel(),
                'topic' => $exercise->topic?->only('name', 'color', 'text_color'),
            ]),
            'nextExerciseId' => $new->first()?->id,
        ]);
    }

    /**
     * The student's status on an exercise; no progress yet means it is new.
     */
    private function status(Exercise $exercise): ProgressStatus
    {
        return $exercise->progress->first()->status ?? ProgressStatus::New;
    }
}
