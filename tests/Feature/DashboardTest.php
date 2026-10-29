<?php

namespace Tests\Feature;

use App\Enums\ProgressStatus;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\Progress;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BadgeSeeder::class);
    }

    public function test_a_new_student_sees_the_empty_state(): void
    {
        $exercises = Exercise::factory()->count(4)->sequence(fn ($sequence) => ['published_at' => now()->subDays(4 - $sequence->index)])->create();

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Dashboard')
                ->where('auth.user.stars', 0)
                ->where('nextBadge', ['name' => 'Nieuwsgierig', 'threshold' => 5])
                ->has('badges', 5)
                ->where('badges.0.earned', false)
                ->where('exercisesDone', 0)
                ->where('exercisesTotal', 4)
                ->where('suggestions', fn ($suggestions) => $suggestions->pluck('id')->all() === $exercises->take(3)->pluck('id')->all())
                ->where('suggestions.0.status', 'new')
                ->where('nextExerciseId', $exercises->first()?->id));
    }

    public function test_the_next_badge_and_earned_badges_follow_the_student(): void
    {
        $student = User::factory()->create(['stars' => 23]);
        $student->badges()->attach(Badge::query()->where('threshold', '<=', 23)->pluck('id'), ['awarded_at' => now()]);

        $this->actingAs($student)
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nextBadge', ['name' => 'Kenner', 'threshold' => 30])
                ->where('badges.0.earned', true)
                ->where('badges.1.earned', true)
                ->where('badges.2.earned', false));
    }

    public function test_there_is_no_next_badge_once_all_badges_are_earned(): void
    {
        $this->actingAs(User::factory()->create(['stars' => 80]))
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('nextBadge', null));
    }

    public function test_retries_come_before_new_exercises_with_at_most_three_suggestions(): void
    {
        $student = User::factory()->create();
        [$new, $retry, $done, $otherNew] = Exercise::factory()->count(4)->create(['stars' => 4]);
        Exercise::factory()->unpublished()->create();
        // Another student's AI exercise does not count either.
        Exercise::factory()->forSubmission()->create();

        Progress::factory()->retry()->create(['user_id' => $student->id, 'exercise_id' => $retry->id, 'best_stars' => 1]);
        Progress::factory()->done()->create(['user_id' => $student->id, 'exercise_id' => $done->id]);
        // Another student's progress does not count.
        Progress::factory()->done()->create(['exercise_id' => $new->id]);

        $this->actingAs($student)
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('exercisesDone', 1)
                ->where('exercisesTotal', 4)
                ->has('suggestions', 3)
                ->where('suggestions.0.id', $retry->id)
                ->where('suggestions.0.status', 'retry')
                ->where('suggestions.0.statusLabel', ProgressStatus::Retry->getLabel())
                ->where('suggestions.0.bestStars', 1)
                ->where('suggestions.0.stars', 4)
                ->where('suggestions.1.id', $new->id)
                ->where('suggestions.2.id', $otherNew->id)
                ->where('nextExerciseId', $new->id));
    }

    public function test_there_is_no_next_exercise_when_nothing_new_is_left(): void
    {
        $student = User::factory()->create();
        Progress::factory()->done()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->get('/')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('suggestions', 0)
                ->where('nextExerciseId', null));
    }
}
