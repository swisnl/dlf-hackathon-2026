<?php

namespace Tests\Feature\Badges;

use App\Models\Badge;
use App\Models\BadgeUser;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class BadgeMomentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_new_badges_are_shared_lowest_first_with_the_next_badge_to_earn(): void
    {
        $student = User::factory()->create(['stars' => 20]);

        $this->actingAs($student)
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('newBadges', 2)
                ->where('newBadges.0.name', 'Nieuwsgierig')
                ->where('newBadges.0.threshold', 5)
                ->where('newBadges.0.next', ['name' => 'Kenner', 'threshold' => 30])
                ->where('newBadges.1.name', 'Speurder'));
    }

    public function test_there_is_no_next_badge_once_all_badges_are_earned(): void
    {
        $this->actingAs(User::factory()->create(['stars' => 80]))
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('newBadges', 5)
                ->where('newBadges.4.next', null));
    }

    public function test_marking_a_badge_as_seen_shows_it_only_once(): void
    {
        $student = User::factory()->create(['stars' => 20]);
        $badge = Badge::query()->where('name', 'Nieuwsgierig')->sole();

        $this->actingAs($student)
            ->from('/exercises')
            ->post("/badges/{$badge->id}/seen")
            ->assertRedirect('/exercises');

        $this->assertNotNull($this->award($student, $badge)->seen_at);

        $this->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('newBadges', 1)
                ->where('newBadges.0.name', 'Speurder'));
    }

    public function test_seeing_a_badge_again_keeps_the_first_time(): void
    {
        $student = User::factory()->create(['stars' => 5]);
        $badge = Badge::query()->where('name', 'Nieuwsgierig')->sole();

        $this->travelTo('2026-10-29 15:00:00');
        $this->actingAs($student)->post("/badges/{$badge->id}/seen");
        $this->travel(1)->hour();
        $this->post("/badges/{$badge->id}/seen");

        $this->assertSame('2026-10-29 15:00:00', $this->award($student, $badge)->seen_at?->toDateTimeString());
    }

    public function test_the_pop_up_can_continue_to_the_badges_or_the_exercises(): void
    {
        [$first, $second] = Badge::query()->orderBy('threshold')->get();

        $this->actingAs(User::factory()->create(['stars' => 20]));

        $this->post("/badges/{$first->id}/seen", ['then' => 'badges.index'])->assertRedirect('/badges');
        $this->post("/badges/{$second->id}/seen", ['then' => 'exercises.index'])->assertRedirect('/exercises');
    }

    public function test_the_pop_up_cannot_redirect_anywhere_else(): void
    {
        $badge = Badge::query()->where('name', 'Nieuwsgierig')->sole();

        $this->actingAs(User::factory()->create(['stars' => 5]))
            ->post("/badges/{$badge->id}/seen", ['then' => 'profile.edit'])
            ->assertSessionHasErrors('then');
    }

    public function test_students_only_mark_their_own_badges_as_seen(): void
    {
        $other = User::factory()->create(['stars' => 5]);
        $badge = Badge::query()->where('name', 'Nieuwsgierig')->sole();

        $this->actingAs(User::factory()->create(['stars' => 5]))->post("/badges/{$badge->id}/seen");

        $this->assertNull($this->award($other, $badge)->seen_at);
    }

    public function test_guests_cannot_mark_badges_as_seen(): void
    {
        $badge = Badge::query()->firstOrFail();

        $this->post("/badges/{$badge->id}/seen")->assertRedirect('/login');
    }

    private function award(User $student, Badge $badge): BadgeUser
    {
        return BadgeUser::query()->whereBelongsTo($student)->whereBelongsTo($badge)->sole();
    }
}
