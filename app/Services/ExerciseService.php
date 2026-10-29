<?php

namespace App\Services;

use App\Ai\Agents\AnswerAssessor;
use App\Ai\Agents\ExerciseGenerator;
use App\Ai\Agents\ExerciseTutor;
use App\Enums\ExerciseMessageRole;
use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\ExerciseMessage;
use App\Models\Submission;
use Illuminate\Support\Lottery;

class ExerciseService
{
    /**
     * Let the AI generate a new exercise about the submitted code.
     */
    public function generate(Submission $submission): Exercise
    {
        /** @var list<string> $previousConcepts */
        $previousConcepts = $submission->exercises()->pluck('concept')->all();

        $generator = new ExerciseGenerator($submission, $previousConcepts);
        $response = $generator->prompt($generator->toPrompt());

        // The AI tends to put the correct answer first, so shuffle the variants ourselves.
        $correct = Lottery::odds(1, 2)->winner(fn () => Variant::A)->loser(fn () => Variant::B)->choose();
        $correctCode = $this->string($response['correct_code']);
        $incorrectCode = $this->string($response['incorrect_code']);

        $exercise = new Exercise([
            'concept' => $this->string($response['concept']),
            'question' => $this->string($response['question']),
            'variant_a' => $correct === Variant::A ? $correctCode : $incorrectCode,
            'variant_b' => $correct === Variant::B ? $correctCode : $incorrectCode,
            'correct_variant' => $correct,
            'reference_explanation' => $this->string($response['reference_explanation']),
        ]);
        $exercise->user()->associate($submission->user);
        $submission->exercises()->save($exercise);

        return $exercise;
    }

    /**
     * Store the student's answer and let the AI assess the choice and explanation.
     */
    public function answer(Exercise $exercise, Variant $choice, string $explanation): Exercise
    {
        $exercise->chosen_variant = $choice;
        $exercise->explanation = $explanation;
        $exercise->is_correct = $choice === $exercise->correct_variant;

        $assessor = new AnswerAssessor($exercise);
        $response = $assessor->prompt($assessor->toPrompt());

        $score = $response['score'];
        $exercise->score = max(0, min(10, is_numeric($score) ? (int) $score : 0));
        $exercise->feedback = $this->string($response['feedback']);
        $exercise->answered_at = now();
        $exercise->save();

        return $exercise;
    }

    /**
     * Store the student's reaction and the tutor's reply.
     */
    public function reply(Exercise $exercise, string $content): ExerciseMessage
    {
        $exercise->messages()->create(['role' => ExerciseMessageRole::Student, 'content' => $content]);

        $response = (new ExerciseTutor($exercise))->prompt($content);

        return $exercise->messages()->create(['role' => ExerciseMessageRole::Tutor, 'content' => $response->text]);
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
