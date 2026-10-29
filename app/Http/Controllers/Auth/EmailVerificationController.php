<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|Response
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('Auth/VerifyEmail');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()?->sendEmailVerificationNotification();

        return back()->with('status', __('auth.verification_link_sent'));
    }
}
