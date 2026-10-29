<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AppPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    #[DataProvider('roles')]
    public function test_every_role_can_access_the_app(UserRole $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));

        $this->get('/')
            ->assertOk()
            ->assertSee($role->getLabel());
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function roles(): array
    {
        return [
            'student' => [UserRole::Student],
            'teacher' => [UserRole::Teacher],
            'admin' => [UserRole::Admin],
        ];
    }

    public function test_registered_users_become_students(): void
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Student',
                'email' => 'student@example.com',
                'password' => 'geheim-wachtwoord',
                'passwordConfirmation' => 'geheim-wachtwoord',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'student@example.com')->sole();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertAuthenticatedAs($user);
    }
}
