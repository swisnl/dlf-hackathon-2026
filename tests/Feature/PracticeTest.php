<?php

namespace Tests\Feature;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\ExerciseError;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PracticeTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Exercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create();
        $this->exercise = Exercise::factory()->create(['stars' => 3]);
        // The factory's wrong code calls belongsTo() on line 5.
        ExerciseError::factory()->for($this->exercise)->create(['line' => 5]);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get("/oefeningen/{$this->exercise->id}")->assertRedirect('/login');
    }

    public function test_the_page_shows_both_classes_highlighted_without_giving_away_the_answer(): void
    {
        $wrong = $this->exercise->correctVariantFor($this->student)->other()->value;

        $response = $this->actingAs($this->student)->get("/oefeningen/{$this->exercise->id}");

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Exercises/Practice')
                ->where('exercise.id', $this->exercise->id)
                ->where('exercise.stars', 3)
                ->has('exercise.topic.name')
                ->has('classes', 2)
                ->where('classes.0.value', 'a')
                ->where('classes.1.value', 'b')
                ->where('classes.0.file', 'Post.php')
                ->has('classes.0.lines', 7)
                ->where("classes.{$this->indexOf($wrong)}.lines.4", fn ($pieces): bool => str_contains(collect($pieces)->implode('text'), 'belongsTo'))
                ->where('classes.0.lines.0.0', ['text' => 'class', 'color' => '#1d4ed8'])
                ->where('attempt', 1)
                ->where('maxAttempts', 2)
                ->where('completed', false));

        // Only the code: no error lines, hints, explanations or which side is correct.
        $page = json_encode($response->viewData('page'), JSON_THROW_ON_ERROR);
        $error = $this->exercise->errors->first();
        $this->assertNotNull($error);
        $this->assertStringNotContainsString($error->hint, $page);
        $this->assertStringNotContainsString($error->explanation, $page);
        $this->assertStringNotContainsString('correct', $page);
        $this->assertStringNotContainsString('wrong', $page);
    }

    public function test_the_wrong_class_is_not_on_the_same_side_for_every_student(): void
    {
        $variants = User::factory()->count(20)->create()
            ->map(fn (User $student): Variant => $this->exercise->correctVariantFor($student))
            ->unique();

        $this->assertCount(2, $variants);
    }

    public function test_unpublished_exercises_and_exercises_about_other_code_are_not_found(): void
    {
        $this->actingAs($this->student);

        $this->get('/oefeningen/'.Exercise::factory()->unpublished()->create()->id)->assertNotFound();
        $this->get('/oefeningen/'.Exercise::factory()->forSubmission()->create()->id)->assertNotFound();
    }

    public function test_the_header_shows_the_second_attempt_after_the_first(): void
    {
        $this->submit([5, 1]);

        $this->actingAs($this->student)
            ->get("/oefeningen/{$this->exercise->id}")
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('attempt', 2)
                ->where('completed', false));
    }

    public function test_a_completed_exercise_is_shown_as_completed(): void
    {
        Progress::factory()->done()->create(['user_id' => $this->student->id, 'exercise_id' => $this->exercise->id]);

        $this->actingAs($this->student)
            ->get("/oefeningen/{$this->exercise->id}")
            ->assertInertia(fn (AssertableInertia $page) => $page->where('completed', true));
    }

    public function test_submitting_grades_the_lines_in_the_chosen_class(): void
    {
        $this->submit([5])->assertRedirect("/oefeningen/{$this->exercise->id}");

        $attempt = $this->student->attempts()->sole();
        $this->assertSame(CodeSide::Wrong, $attempt->chosen_side);
        $this->assertSame([5], $attempt->selected_lines);
        $this->assertSame(3, $attempt->stars);
        $this->assertSame(3, $this->student->fresh()?->stars);
    }

    public function test_lines_in_the_correct_class_earn_no_stars(): void
    {
        $this->actingAs($this->student)->post("/oefeningen/{$this->exercise->id}/pogingen", [
            'choice' => $this->exercise->correctVariantFor($this->student)->value,
            'lines' => [5],
        ]);

        $attempt = $this->student->attempts()->sole();
        $this->assertSame(CodeSide::Correct, $attempt->chosen_side);
        $this->assertSame(0, $attempt->stars);
    }

    public function test_an_attempt_needs_a_class_and_existing_lines(): void
    {
        $this->actingAs($this->student)
            ->post("/oefeningen/{$this->exercise->id}/pogingen", ['choice' => 'c', 'lines' => []])
            ->assertSessionHasErrors(['choice', 'lines']);

        $this->submit([0, 8])->assertSessionHasErrors(['lines.0', 'lines.1']);
        $this->submit([5, 5])->assertSessionHasErrors('lines.0');

        $this->assertSame(0, $this->student->attempts()->count());
    }

    public function test_a_completed_exercise_cannot_be_submitted_again(): void
    {
        $this->submit([5]);

        $this->submit([5])
            ->assertRedirect("/oefeningen/{$this->exercise->id}")
            ->assertSessionHas('error', 'Deze oefening heb je al afgerond.');

        $this->assertSame(1, $this->student->attempts()->count());
        $this->assertSame(ProgressStatus::Done, Progress::query()->sole()->status);
    }

    /**
     * Submit the lines in the class that is wrong for the student.
     *
     * @param list<int> $lines
     */
    private function submit(array $lines): TestResponse
    {
        return $this->actingAs($this->student)->post("/oefeningen/{$this->exercise->id}/pogingen", [
            'choice' => $this->exercise->correctVariantFor($this->student)->other()->value,
            'lines' => $lines,
        ]);
    }

    private function indexOf(string $variant): int
    {
        return $variant === Variant::A->value ? 0 : 1;
    }
}
