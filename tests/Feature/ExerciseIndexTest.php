<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\Progress;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ExerciseIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/exercises')->assertRedirect('/login');
    }

    public function test_only_published_exercises_are_shown_with_the_students_status_and_stars(): void
    {
        $student = User::factory()->create();
        [$new, $retry, $done] = Exercise::factory()->count(3)->sequence(fn ($sequence) => ['published_at' => now()->subDays(3 - $sequence->index)])->create(['stars' => 3]);
        Exercise::factory()->unpublished()->create();

        Progress::factory()->retry()->create(['user_id' => $student->id, 'exercise_id' => $retry->id, 'best_stars' => 1]);
        Progress::factory()->done()->create(['user_id' => $student->id, 'exercise_id' => $done->id, 'best_stars' => 2]);
        // Another student's progress does not count.
        Progress::factory()->done()->create(['exercise_id' => $new->id]);

        $this->actingAs($student)
            ->get('/exercises')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Exercises/Index')
                ->has('exercises', 3)
                ->where('exercises.0.id', $new->id)
                ->where('exercises.0.status', 'new')
                ->where('exercises.0.bestStars', 0)
                ->where('exercises.1.id', $retry->id)
                ->where('exercises.1.status', 'retry')
                ->where('exercises.1.bestStars', 1)
                ->where('exercises.2.id', $done->id)
                ->where('exercises.2.status', 'done')
                ->where('exercises.2.bestStars', 2)
                ->where('filters', ['topic' => null, 'status' => null]));
    }

    public function test_exercises_can_be_filtered_by_topic(): void
    {
        $eloquent = Topic::factory()->create(['name' => 'Eloquent']);
        $routes = Topic::factory()->create(['name' => 'Routes']);
        $exercise = Exercise::factory()->for($eloquent)->create();
        Exercise::factory()->for($routes)->create();

        $this->actingAs(User::factory()->create())
            ->get('/exercises?topic=Eloquent')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('topics', ['Eloquent', 'Routes'])
                ->where('filters.topic', 'Eloquent')
                ->has('exercises', 1)
                ->where('exercises.0.id', $exercise->id));
    }

    public function test_exercises_can_be_filtered_by_status(): void
    {
        $student = User::factory()->create();
        Exercise::factory()->create();
        $retry = Progress::factory()->retry()->create(['user_id' => $student->id]);
        Progress::factory()->done()->create(['user_id' => $student->id]);

        $this->actingAs($student)
            ->get('/exercises?status=retry')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.status', 'retry')
                ->has('exercises', 1)
                ->where('exercises.0.id', $retry->exercise_id));
    }

    public function test_an_unknown_status_is_ignored(): void
    {
        Exercise::factory()->count(2)->create();

        $this->actingAs(User::factory()->create())
            ->get('/exercises?status=bogus')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('filters.status', null)
                ->has('exercises', 2));
    }
}
