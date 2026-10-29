<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_registration_page_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/Register'));
    }

    public function test_new_users_are_students_and_must_verify_their_email(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Daan de Vries',
            'email' => 'daan@student.school.nl',
            'password' => 'geheimwachtwoord',
            'password_confirmation' => 'geheimwachtwoord',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'daan@student.school.nl')->sole();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_a_role_cannot_be_chosen_when_registering(): void
    {
        $this->post('/register', [
            'name' => 'Daan de Vries',
            'email' => 'daan@student.school.nl',
            'password' => 'geheimwachtwoord',
            'password_confirmation' => 'geheimwachtwoord',
            'role' => UserRole::Admin->value,
        ]);

        $this->assertSame(UserRole::Student, User::query()->sole()->role);
    }

    public function test_the_email_address_must_be_unique(): void
    {
        $user = User::factory()->create();

        $this->post('/register', [
            'name' => 'Daan de Vries',
            'email' => $user->email,
            'password' => 'geheimwachtwoord',
            'password_confirmation' => 'geheimwachtwoord',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_the_password_must_be_repeated(): void
    {
        $this->post('/register', [
            'name' => 'Daan de Vries',
            'email' => 'daan@student.school.nl',
            'password' => 'geheimwachtwoord',
            'password_confirmation' => 'anderwachtwoord',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_validation_messages_are_dutch(): void
    {
        $this->app->setLocale('nl');

        $this->post('/register', [])->assertSessionHasErrors([
            'name' => 'Naam is verplicht.',
            'email' => 'E-mailadres is verplicht.',
            'password' => 'Wachtwoord is verplicht.',
        ]);
    }
}
