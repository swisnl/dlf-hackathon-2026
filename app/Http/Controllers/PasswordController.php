<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $this->user($request);

        $user->update([
            'password' => $request->string('password')->value(),
        ]);

        return back()->with('status', __('profile.password_updated'));
    }
}
