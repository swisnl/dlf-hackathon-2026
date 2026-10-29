<?php

namespace Database\Seeders;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Grading\AttemptGrader;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DemoProgressSeeder extends Seeder
{
    /**
     * Let most students attempt the example exercise through the real grader, then top up their stars
     * and award the matching badges, so the teacher's progress overview has something to show.
     */
    public function run(AttemptGrader $grader): void
    {
        $exercise = Exercise::query()->with('errors')->firstOrFail();
        $badges = Badge::query()->get();

        Student::query()->inRandomOrder()->limit(20)->get()->each(function (Student $student) use ($grader, $exercise, $badges): void {
            $grader->grade($student, $exercise, CodeSide::Wrong, $this->selectLines($exercise));

            // After an imperfect first attempt: try again, give up, or leave it for a retry later.
            if ($student->progress()->where('exercise_id', $exercise->id)->sole()->status === ProgressStatus::Retry) {
                match (fake()->numberBetween(1, 3)) {
                    1 => $grader->grade($student, $exercise, CodeSide::Wrong, $this->selectLines($exercise)),
                    2 => $grader->giveUp($student, $exercise),
                    default => null,
                };
            }

            // Stars from exercises the demo does not have yet, so some students reach a badge.
            $student->increment('stars', fake()->numberBetween(0, 40));

            $student->badges()->attach(
                $badges->where('threshold', '<=', $student->stars)->pluck('id'),
                ['awarded_at' => now()],
            );
        });
    }

    /**
     * Some of the error lines plus up to two lines that are not an error.
     *
     * @return array<int, int>
     */
    private function selectLines(Exercise $exercise): array
    {
        $errorLines = collect($exercise->errorLines());
        $otherLines = collect(range(1, count(explode("\n", $exercise->wrong_code))))->reject(fn (int $line): bool => $errorLines->contains($line));

        return $errorLines->random(fake()->numberBetween(0, $errorLines->count()))
            ->merge($otherLines->random(fake()->numberBetween(0, 2)))
            ->values()
            ->all();
    }
}
