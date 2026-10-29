<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->safe()->only('email'));

        // Always the same message, so the form doesn't reveal which email addresses have an account.
        return back()->with('status', __('passwords.sent'));
    }
}
