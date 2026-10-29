<?php

namespace App\Grading;

use App\Enums\CodeSide;
use App\Enums\ProgressStatus;
use App\Exceptions\ExerciseAlreadyCompleted;
use App\Exceptions\NothingToGiveUp;
use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Grades attempts on the server and keeps the student's progress and stars in step with them.
 */
final class AttemptGrader
{
    /**
     * Grade an attempt, store it and award only the stars that improve on the best attempt so far.
     *
     * @param array<int> $selectedLines
     *
     * @throws ExerciseAlreadyCompleted
     */
    public function grade(User $student, Exercise $exercise, CodeSide $side, array $selectedLines): Attempt
    {
        return DB::transaction(function () use ($student, $exercise, $side, $selectedLines): Attempt {
            $progress = $this->lockProgress($student, $exercise);
            $number = $student->attempts()->where('exercise_id', $exercise->id)->count() + 1;

            $grade = Grade::calculate(
                $exercise->stars,
                $exercise->errorLines(),
                $side,
                $selectedLines,
                $number,
            );

            $attempt = $student->attempts()->create([
                'exercise_id' => $exercise->id,
                'number' => $number,
                'chosen_side' => $side,
                'selected_lines' => $grade->selectedLines,
                'found' => $grade->found,
                'wrong' => $grade->wrong,
                'stars' => $grade->stars,
            ]);

            $student->increment('stars', max(0, $grade->stars - $progress->best_stars));

            $progress->update([
                'best_stars' => max($progress->best_stars, $grade->stars),
                'status' => $grade->completes ? ProgressStatus::Done : ProgressStatus::Retry,
                'completed_at' => $grade->completes ? now() : null,
            ]);

            return $attempt;
        });
    }

    /**
     * Give up after a first attempt: the exercise is completed with the stars earned so far.
     *
     * @throws ExerciseAlreadyCompleted
     * @throws NothingToGiveUp
     */
    public function giveUp(User $student, Exercise $exercise): Attempt
    {
        return DB::transaction(function () use ($student, $exercise): Attempt {
            $progress = $this->lockProgress($student, $exercise);

            $attempt = $student->attempts()->where('exercise_id', $exercise->id)->latest('number')->first()
                ?? throw new NothingToGiveUp();

            $attempt->update(['gave_up' => true]);
            $progress->update([
                'status' => ProgressStatus::Done,
                'completed_at' => now(),
            ]);

            return $attempt;
        });
    }

    /**
     * Lock the progress row so the same attempt sent twice cannot be graded twice.
     *
     * @throws ExerciseAlreadyCompleted
     */
    private function lockProgress(User $student, Exercise $exercise): Progress
    {
        $progress = Progress::query()
            ->lockForUpdate()
            ->firstOrCreate(
                ['user_id' => $student->id, 'exercise_id' => $exercise->id],
                ['status' => ProgressStatus::New, 'best_stars' => 0],
            );

        if ($progress->status === ProgressStatus::Done) {
            throw new ExerciseAlreadyCompleted();
        }

        return $progress;
    }
}
