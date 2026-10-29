<?php

namespace App\Http\Controllers;

use App\Http\Requests\BadgeSeenRequest;
use App\Models\Badge;
use App\Models\BadgeUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->user($request);

        $awarded = BadgeUser::query()
            ->whereBelongsTo($user)
            ->get()
            ->mapWithKeys(fn (BadgeUser $award): array => [$award->badge_id => $award->awarded_at]);

        return Inertia::render('Badges/Index', [
            'badges' => Badge::query()->orderBy('threshold')->get()->map(fn (Badge $badge): array => [
                ...$badge->only('id', 'name', 'threshold', 'icon', 'color'),
                'earned' => $awarded->has($badge->id),
                'awardedAt' => $awarded->get($badge->id)?->toDateString(),
            ]),
        ]);
    }

    /**
     * The student saw the pop-up for a new badge; it is shown only once.
     */
    public function seen(BadgeSeenRequest $request, Badge $badge): RedirectResponse
    {
        BadgeUser::query()
            ->whereBelongsTo($this->user($request))
            ->whereBelongsTo($badge)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $then = $request->then();

        return $then !== null ? to_route($then) : back();
    }
}
