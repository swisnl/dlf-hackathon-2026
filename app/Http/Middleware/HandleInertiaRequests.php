<?php

namespace App\Http\Middleware;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn (): ?array => $request->user()
                    ? [...$request->user()->only('id', 'name', 'email', 'role', 'stars'), 'initials' => $request->user()->initials()]
                    : null,
            ],
            'status' => fn (): mixed => $request->session()->get('status'),
            'flash' => [
                'error' => fn (): mixed => $request->session()->get('error'),
            ],
            'newBadges' => fn (): array => $request->user() instanceof User ? $this->newBadges($request->user()) : [],
        ];
    }

    /**
     * The badges the student earned but has not seen the pop-up for yet, lowest first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function newBadges(User $user): array
    {
        $badges = $user->badges()->wherePivotNull('seen_at')->orderBy('threshold')->get();

        if ($badges->isEmpty()) {
            return [];
        }

        $next = Badge::query()->where('threshold', '>', $user->stars)->orderBy('threshold')->first();

        return $badges->map(fn (Badge $badge): array => [
            ...$badge->only('id', 'name', 'threshold', 'icon', 'color'),
            'next' => $next?->only('name', 'threshold'),
        ])->values()->all();
    }
}
