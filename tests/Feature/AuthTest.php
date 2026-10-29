<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('submissions.index'))->assertRedirect(route('login'));
    }

    public function test_a_student_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'password' => 'geheim-wachtwoord',
            'password_confirmation' => 'geheim-wachtwoord',
        ])->assertRedirect(route('submissions.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'sam@example.com']);
    }

    public function test_a_student_can_log_in_and_out(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('submissions.index'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_a_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'fout'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
