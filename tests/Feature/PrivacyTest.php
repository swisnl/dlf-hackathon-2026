<?php

namespace Tests\Feature;

use App\Filament\AvatarProviders\InitialsAvatarProvider;
use App\Models\User;
use App\Session\DatabaseSessionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_read_the_privacy_statement(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Privacy'));
    }

    public function test_signed_in_students_can_read_the_privacy_statement(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/privacy')
            ->assertOk();
    }

    public function test_sessions_do_not_store_the_ip_address_or_user_agent(): void
    {
        config(['session.driver' => 'database']);

        $handler = $this->app->make('session')->driver('database')->getHandler();
        $this->assertInstanceOf(DatabaseSessionHandler::class, $handler);

        $this->assertTrue($handler->write('session-id', 'payload'));
        $this->assertSame(['id', 'user_id', 'payload', 'last_activity'], Schema::getColumnListing('sessions'));
        $this->assertDatabaseHas('sessions', ['id' => 'session-id']);
    }

    public function test_deleting_an_account_deletes_its_sessions(): void
    {
        $user = User::factory()->create();
        DB::table('sessions')->insert(['id' => 'session-id', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $user->delete();

        $this->assertDatabaseMissing('sessions', ['id' => 'session-id']);
    }

    public function test_avatars_are_drawn_locally(): void
    {
        $avatar = (new InitialsAvatarProvider())->get(User::factory()->make(['name' => 'Jamie de Vries']));

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $avatar);
        $this->assertStringContainsString('>JD<', base64_decode(substr($avatar, strlen('data:image/svg+xml;base64,'))));
    }
}
