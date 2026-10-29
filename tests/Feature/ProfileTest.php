<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/profiel')->assertRedirect('/login');
    }

    public function test_the_profile_card_shows_the_students_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Daan Bakker',
            'email' => 'daan@student.school.nl',
            'stars' => 23,
            'created_at' => Carbon::parse('2026-10-01'),
        ]);
        $user->badges()->attach(Badge::factory()->create(['name' => 'Nieuwsgierig', 'threshold' => 5]), ['awarded_at' => now()]);
        $user->badges()->attach(Badge::factory()->create(['name' => 'Speurder', 'threshold' => 15]), ['awarded_at' => now()]);
        Badge::factory()->create(['name' => 'Kenner', 'threshold' => 30]);

        $this->actingAs($user)
            ->get('/profiel')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Profile/Edit')
                ->where('profile', [
                    'name' => 'Daan Bakker',
                    'email' => 'daan@student.school.nl',
                    'initials' => 'DB',
                    'memberSince' => '1 oktober 2026',
                    'stars' => 23,
                    'highestBadge' => 'Speurder',
                ])
                ->where('auth.user.initials', 'DB'));
    }

    public function test_the_highest_badge_is_empty_without_badges(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/profiel')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('profile.highestBadge', null));
    }

    public function test_the_name_can_be_changed(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/profiel')
            ->patch('/profiel', ['name' => 'Daan de Vries', 'email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profiel');

        $user->refresh();
        $this->assertSame('Daan de Vries', $user->name);
        $this->assertTrue($user->hasVerifiedEmail());
        Notification::assertNothingSent();
    }

    public function test_a_new_email_address_has_to_be_verified_again(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profiel', ['name' => $user->name, 'email' => 'nieuw@student.school.nl'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('nieuw@student.school.nl', $user->email);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_an_unverified_student_can_still_reach_the_profile(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get('/profiel')
            ->assertOk();
    }

    public function test_the_email_address_must_be_unique(): void
    {
        User::factory()->create(['email' => 'bezet@student.school.nl']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch('/profiel', ['name' => $user->name, 'email' => 'bezet@student.school.nl'])
            ->assertSessionHasErrors('email');
    }

    public function test_the_password_can_be_changed_with_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profiel/wachtwoord', [
                'current_password' => 'password',
                'password' => 'nieuw-wachtwoord',
                'password_confirmation' => 'nieuw-wachtwoord',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nieuw-wachtwoord', $user->refresh()->password));
    }

    public function test_the_password_is_not_changed_with_a_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profiel/wachtwoord', [
                'current_password' => 'fout',
                'password' => 'nieuw-wachtwoord',
                'password_confirmation' => 'nieuw-wachtwoord',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
