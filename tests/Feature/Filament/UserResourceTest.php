<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admins_cannot_access_the_panel(UserRole $role): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));

        $this->get('/admin')->assertForbidden();
        $this->get(UserResource::getUrl('index'))->assertForbidden();
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function nonAdminRoles(): array
    {
        return [
            'student' => [UserRole::Student],
            'teacher' => [UserRole::Teacher],
        ];
    }

    public function test_admins_can_list_users(): void
    {
        $users = User::factory()->count(3)->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ListUsers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($users);
    }

    public function test_admins_can_create_users(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Nieuwe Beheerder',
                'email' => 'beheerder@example.com',
                'password' => 'geheim-wachtwoord',
                'role' => UserRole::Admin,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(User::class, [
            'email' => 'beheerder@example.com',
            'role' => UserRole::Admin->value,
        ]);
    }

    public function test_admins_can_change_the_role_without_changing_the_password(): void
    {
        $student = User::factory()->create();
        $password = $student->password;

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(EditUser::class, ['record' => $student->getRouteKey()])
            ->fillForm(['role' => UserRole::Admin])
            ->call('save')
            ->assertHasNoFormErrors();

        $student->refresh();
        $this->assertSame(UserRole::Admin, $student->role);
        $this->assertSame($password, $student->password);
    }

    public function test_admins_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $admin->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_new_users_are_students_by_default(): void
    {
        $this->assertSame(UserRole::Student, User::factory()->create()->role);
    }
}
