<?php

namespace Tests\Feature\Grading;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Exceptions\ExerciseAlreadyCompleted;
use App\Exceptions\NothingToGiveUp;
use App\Grading\AttemptGrader;
use App\Models\Exercise;
use App\Models\ExerciseError;
use App\Models\Progress;
use App\Models\User;
use Database\Seeders\BadgeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptGraderTest extends TestCase
{
    use RefreshDatabase;

    private AttemptGrader $grader;

    private User $student;

    private Exercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grader = new AttemptGrader();
        $this->student = User::factory()->create();
        $this->exercise = Exercise::factory()->create(['stars' => 3]);

        foreach ([4, 8, 12] as $line) {
            ExerciseError::factory()->for($this->exercise)->create(['line' => $line]);
        }
    }

    public function test_a_flawless_first_attempt_completes_the_exercise(): void
    {
        $attempt = $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8, 12]);

        $this->assertSame([1, 3, 0, 3], [$attempt->number, $attempt->found, $attempt->wrong, $attempt->stars]);
        $this->assertSame(3, $this->student->fresh()->stars);
        $this->assertProgress(ProgressStatus::Done, 3, completed: true);
    }

    public function test_an_imperfect_first_attempt_leaves_room_for_a_retry(): void
    {
        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8]);

        $this->assertSame(2, $this->student->fresh()->stars);
        $this->assertProgress(ProgressStatus::Retry, 2, completed: false);
    }

    public function test_the_second_attempt_only_adds_stars_that_improve_the_best_score(): void
    {
        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4]);
        $attempt = $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8, 12]);

        $this->assertSame([2, 2], [$attempt->number, $attempt->stars]);
        $this->assertSame(2, $this->student->fresh()->stars);
        $this->assertProgress(ProgressStatus::Done, 2, completed: true);
    }

    public function test_a_worse_second_attempt_keeps_the_best_score(): void
    {
        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8]);
        $this->grader->grade($this->student, $this->exercise, CodeSide::Correct, [1]);

        $this->assertSame(2, $this->student->fresh()->stars);
        $this->assertProgress(ProgressStatus::Done, 2, completed: true);
    }

    public function test_a_completed_exercise_earns_no_more_stars(): void
    {
        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8, 12]);

        $this->expectException(ExerciseAlreadyCompleted::class);

        try {
            $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8, 12]);
        } finally {
            $this->assertSame(3, $this->student->fresh()->stars);
            $this->assertSame(1, $this->student->attempts()->count());
        }
    }

    public function test_giving_up_completes_the_exercise_with_the_stars_so_far(): void
    {
        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4]);
        $attempt = $this->grader->giveUp($this->student, $this->exercise);

        $this->assertTrue($attempt->gave_up);
        $this->assertSame(1, $this->student->fresh()->stars);
        $this->assertProgress(ProgressStatus::Done, 1, completed: true);
    }

    public function test_giving_up_requires_a_first_attempt(): void
    {
        $this->expectException(NothingToGiveUp::class);

        $this->grader->giveUp($this->student, $this->exercise);
    }

    public function test_attempts_are_counted_per_student(): void
    {
        $this->grader->grade(User::factory()->create(), $this->exercise, CodeSide::Wrong, [4]);

        $attempt = $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4]);

        $this->assertSame(1, $attempt->number);
    }

    public function test_reaching_a_threshold_through_an_attempt_awards_the_badge(): void
    {
        $this->seed(BadgeSeeder::class);
        $this->student->update(['stars' => 3]);

        $this->grader->grade($this->student, $this->exercise, CodeSide::Wrong, [4, 8]);

        $this->assertSame(['Nieuwsgierig'], $this->student->badges()->pluck('name')->all());
    }

    private function assertProgress(ProgressStatus $status, int $bestStars, bool $completed): void
    {
        $progress = Progress::query()->where('user_id', $this->student->id)->where('exercise_id', $this->exercise->id)->sole();

        $this->assertSame($status, $progress->status);
        $this->assertSame($bestStars, $progress->best_stars);
        $this->assertSame($completed, $progress->completed_at !== null);
    }
}
