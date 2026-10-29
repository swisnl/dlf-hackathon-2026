<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_users_cannot_reach_the_dashboard(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get('/')
            ->assertRedirect(route('verification.notice'));
    }

    public function test_the_verification_notice_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get('/verify-email')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/VerifyEmail'));
    }

    public function test_users_can_verify_their_email_and_land_on_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user, sha1($user->email)))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()?->hasVerifiedEmail());
    }

    public function test_the_email_is_not_verified_with_an_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user, sha1('someone-else@example.com')))
            ->assertForbidden();

        $this->assertFalse($user->fresh()?->hasVerifiedEmail());
    }

    public function test_the_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post('/email/verification-notification')
            ->assertSessionHas('status');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    private function verificationUrl(User $user, string $hash): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => $hash,
        ]);
    }
}
