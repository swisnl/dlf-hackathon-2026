<?php

namespace Database\Seeders;

use App\Enums\ProgressStatus;
use App\Models\Attempt;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\ExerciseError;
use App\Models\Progress;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DemoProgressSeeder extends Seeder
{
    /**
     * Give most students attempts on the example exercise, stars and the badges that go with them,
     * so the teacher's progress overview has something to show.
     */
    public function run(): void
    {
        $exercise = Exercise::query()->with('errors')->firstOrFail();
        $errorLines = $exercise->errors->map(fn (ExerciseError $error): int => $error->line)->all();
        $badges = Badge::query()->get();

        Student::query()->inRandomOrder()->limit(20)->get()->each(function (Student $student) use ($exercise, $errorLines, $badges): void {
            $first = $this->attempt($student, $exercise, $errorLines, 1);
            $best = $first;

            if ($first->found < count($errorLines)) {
                $best = $this->attempt($student, $exercise, $errorLines, 2);
            }

            Progress::factory()->create([
                'exercise_id' => $exercise->id,
                'user_id' => $student->id,
                'status' => $best->found === count($errorLines) ? ProgressStatus::Done : ProgressStatus::Retry,
                'best_stars' => $best->stars,
                'completed_at' => $best->found === count($errorLines) ? $best->created_at : null,
            ]);

            // Stars from exercises the demo does not have yet, so some students reach a badge.
            $student->update(['stars' => $best->stars + fake()->numberBetween(0, 40)]);

            $student->badges()->attach(
                $badges->where('threshold', '<=', $student->stars)->pluck('id'),
                ['awarded_at' => now()],
            );
        });
    }

    /**
     * @param array<int, int> $errorLines
     */
    private function attempt(Student $student, Exercise $exercise, array $errorLines, int $number): Attempt
    {
        $found = fake()->randomElements($errorLines, fake()->numberBetween(0, count($errorLines)));
        $wrong = fake()->randomElements(array_diff(range(1, 22), $errorLines), fake()->numberBetween(0, 2));
        $selectedLines = [...$found, ...$wrong];
        sort($selectedLines);

        return Attempt::factory()->create([
            'user_id' => $student->id,
            'exercise_id' => $exercise->id,
            'number' => $number,
            'selected_lines' => $selectedLines,
            'found' => count($found),
            'wrong' => count($wrong),
            'stars' => max(0, count($found) - count($wrong)),
        ]);
    }
}
