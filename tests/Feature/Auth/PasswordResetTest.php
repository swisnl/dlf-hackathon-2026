<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_forgot_password_page_can_be_rendered(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/ForgotPassword'));
    }

    public function test_a_reset_link_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_an_unknown_email_gets_the_same_message(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertNothingSent();
    }

    public function test_the_reset_link_is_valid_for_60_minutes(): void
    {
        $this->assertSame(60, config('auth.passwords.users.expire'));
    }

    public function test_the_reset_password_page_can_be_rendered(): void
    {
        $this->get('/reset-password/token?email=daan@student.school.nl')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Auth/ResetPassword')
                ->where('token', 'token')
                ->where('email', 'daan@student.school.nl'));
    }

    public function test_the_password_can_be_reset_and_the_user_is_logged_in(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nieuwwachtwoord',
            'password_confirmation' => 'nieuwwachtwoord',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('nieuwwachtwoord', (string) $user->fresh()?->password));
    }

    public function test_the_password_is_not_reset_with_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'nieuwwachtwoord',
            'password_confirmation' => 'nieuwwachtwoord',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
