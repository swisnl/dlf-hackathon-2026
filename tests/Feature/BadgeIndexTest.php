<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class BadgeIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/badges')->assertRedirect('/login');
    }

    public function test_all_badges_are_shown_in_order_with_the_students_award_dates(): void
    {
        $this->travelTo('2026-10-02 14:00:00');
        $student = User::factory()->create(['stars' => 5]);
        $this->travelTo('2026-10-16 09:30:00');
        $student->increment('stars', 18);

        $this->actingAs($student)
            ->get('/badges')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Badges/Index')
                ->has('badges', 5)
                ->where('badges.0.name', 'Nieuwsgierig')
                ->where('badges.0.earned', true)
                ->where('badges.0.awardedAt', '2026-10-02')
                ->where('badges.1.awardedAt', '2026-10-16')
                ->where('badges.2.name', 'Kenner')
                ->where('badges.2.threshold', 30)
                ->where('badges.2.earned', false)
                ->where('badges.2.awardedAt', null)
                ->where('badges.4.threshold', 75));
    }

    public function test_another_students_badges_do_not_count(): void
    {
        User::factory()->create()->badges()->attach(Badge::query()->pluck('id'), ['awarded_at' => now()]);

        $this->actingAs(User::factory()->create())
            ->get('/badges')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('badges', fn ($badges) => $badges->every(fn (array $badge): bool => !$badge['earned'])));
    }
}
