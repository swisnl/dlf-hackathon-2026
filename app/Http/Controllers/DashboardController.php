<?php

namespace App\Http\Controllers;

use App\Enums\ProgressStatus;
use App\Http\Resources\StudentExerciseResource;
use App\Models\Badge;
use App\Models\Exercise;
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
        $user = $this->user($request);

        $badges = Badge::query()->orderBy('threshold')->get();
        $earnedBadgeIds = $user->badges()->pluck('badges.id');

        $exercises = Exercise::query()
            ->visibleTo($user)
            ->withProgressOf($user)
            ->with('topic')
            ->orderBy('published_at')
            ->orderBy('id')
            ->get();

        $withStatus = fn (ProgressStatus $status) => $exercises->filter(fn (Exercise $exercise): bool => $exercise->statusOfStudent() === $status);
        // The most recently attempted retry first.
        $retries = $withStatus(ProgressStatus::Retry)->sortByDesc(fn (Exercise $exercise) => $exercise->progressOfStudent()?->updated_at);
        $new = $withStatus(ProgressStatus::New);

        return Inertia::render('Dashboard', [
            'badges' => $badges->map(fn (Badge $badge): array => [
                ...$badge->only('id', 'name', 'threshold', 'icon', 'color'),
                'earned' => $earnedBadgeIds->contains($badge->id),
            ]),
            'nextBadge' => $badges->first(fn (Badge $badge): bool => $badge->threshold > $user->stars)?->only('name', 'threshold'),
            'exercisesDone' => $withStatus(ProgressStatus::Done)->count(),
            'exercisesTotal' => $exercises->count(),
            'suggestions' => StudentExerciseResource::collection($retries->concat($new)->take(self::SUGGESTIONS)->values())->resolve(),
            'nextExerciseId' => $new->first()?->id,
        ]);
    }
}
