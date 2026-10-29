<?php

namespace Tests\Feature\Badges;

use App\Models\Badge;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardBadgesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_a_badge_is_awarded_when_the_stars_reach_its_threshold(): void
    {
        $student = User::factory()->create(['stars' => 4]);
        $this->assertSame([], $this->badgesOf($student));

        $this->travelTo('2026-10-29 15:00:00');
        $student->increment('stars');

        $this->assertSame(['Nieuwsgierig'], $this->badgesOf($student));
        $this->assertSame('2026-10-29 15:00:00', $student->badges()->sole()->pivot->awarded_at->toDateTimeString());
    }

    public function test_a_badge_is_awarded_only_once(): void
    {
        $student = User::factory()->create(['stars' => 5]);
        $awardedAt = $student->badges()->sole()->pivot->awarded_at;

        $this->travel(1)->day();
        $student->increment('stars', 2);
        $student->awardBadges();

        $this->assertSame(['Nieuwsgierig'], $this->badgesOf($student));
        $this->assertEquals($awardedAt, $student->badges()->sole()->pivot->awarded_at);
    }

    public function test_jumping_over_several_thresholds_awards_every_badge_on_the_way(): void
    {
        $student = User::factory()->create(['stars' => 3]);

        $student->increment('stars', 30);

        $this->assertSame(['Nieuwsgierig', 'Speurder', 'Kenner'], $this->badgesOf($student));
    }

    public function test_badges_are_never_taken_away(): void
    {
        $student = User::factory()->create(['stars' => 20]);

        $student->update(['stars' => 0]);

        $this->assertSame(['Nieuwsgierig', 'Speurder'], $this->badgesOf($student));
    }

    public function test_the_thresholds_come_from_the_badges_table(): void
    {
        Badge::query()->where('name', 'Nieuwsgierig')->update(['threshold' => 2]);

        $student = User::factory()->create(['stars' => 2]);

        $this->assertSame(['Nieuwsgierig'], $this->badgesOf($student));
    }

    public function test_students_changed_through_the_backoffice_model_earn_badges_too(): void
    {
        $student = Student::query()->findOrFail(User::factory()->create()->id);

        $student->increment('stars', 15);

        $this->assertSame(['Nieuwsgierig', 'Speurder'], $this->badgesOf($student));
    }

    /**
     * @return list<string>
     */
    private function badgesOf(User $student): array
    {
        return $student->badges()->orderBy('threshold')->pluck('name')->all();
    }
}
