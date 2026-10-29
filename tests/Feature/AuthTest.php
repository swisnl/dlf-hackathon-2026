<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('submissions.index'))->assertRedirect(route('login'));
    }

    public function test_login_and_registration_do_not_use_the_house_style_yet(): void
    {
        foreach (['login', 'register'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Code-overhoring')->assertDontSee('eigenwijs');
        }

        $this->actingAs(User::factory()->create())->get(route('submissions.index'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Submissions/Index'));
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

    public function test_the_home_page_is_the_entry_for_students(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Home')->where('auth.user', null));

        $this->actingAs(User::factory()->create(['name' => 'Sam de Vries']))->get('/')
            ->assertInertia(fn (Assert $page) => $page->component('Home')
                ->where('auth.user.name', 'Sam de Vries')
                ->where('auth.user.initials', 'SD'));
    }

    public function test_logging_out_from_an_inertia_page_does_a_full_visit_to_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'), [], ['X-Inertia' => 'true'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('login'));

        $this->assertGuest();
    }
}
