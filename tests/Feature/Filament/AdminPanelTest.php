<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_registration_is_not_available(): void
    {
        $this->get('/admin/register')->assertNotFound();
    }

    #[DataProvider('panelRoles')]
    public function test_teachers_and_admins_can_access_the_panel(UserRole $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));

        $this->get('/admin')
            ->assertOk()
            ->assertSee($role->getLabel());
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function panelRoles(): array
    {
        return [
            'teacher' => [UserRole::Teacher],
            'admin' => [UserRole::Admin],
        ];
    }

    public function test_students_cannot_access_the_panel(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
    }
}
