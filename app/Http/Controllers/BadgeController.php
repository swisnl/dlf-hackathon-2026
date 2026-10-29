<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\BadgeUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BadgeController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

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
}
