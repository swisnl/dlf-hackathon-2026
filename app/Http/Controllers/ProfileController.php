<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'initials' => $user->initials(),
                'memberSince' => $user->created_at?->settings(['locale' => 'nl'])->translatedFormat('j F Y'),
                'stars' => $user->stars,
                'highestBadge' => $user->highestBadge()?->name,
            ],
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->fill($request->validated());

        // A new email address has to be confirmed again.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($user->wasChanged('email')) {
            $user->sendEmailVerificationNotification();

            return back()->with('status', __('profile.email_changed'));
        }

        return back()->with('status', __('profile.updated'));
    }
}
