<?php

namespace App\Services;

use App\Ai\Agents\AnswerAssessor;
use App\Ai\Agents\ExerciseGenerator;
use App\Ai\Agents\ExerciseTutor;
use App\Enums\AttemptMessageRole;
use App\Enums\ProgressStatus;
use App\Enums\Variant;
use App\Exceptions\ExerciseAlreadyAnswered;
use App\Models\Attempt;
use App\Models\AttemptMessage;
use App\Models\Exercise;
use App\Models\Submission;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ExerciseService
{
    /**
     * Stars an exercise about the student's own code is worth.
     */
    public const int STARS = 3;

    /**
     * Let the AI generate a new exercise about the submitted code.
     */
    public function generate(Submission $submission): Exercise
    {
        /** @var array<string, int> $topics */
        $topics = Topic::query()->orderBy('name')->pluck('id', 'name')->all();

        if ($topics === []) {
            throw new RuntimeException('There are no topics to put the exercise in; run the TopicSeeder.');
        }

        /** @var list<string> $previousConcepts */
        $previousConcepts = $submission->exercises()->pluck('title')->all();

        $generator = new ExerciseGenerator($submission, array_keys($topics), $previousConcepts);
        $response = $generator->prompt($generator->toPrompt());

        $correctCode = $this->code($response['correct_code']);
        $wrongCode = $this->code($response['incorrect_code']);

        if ($correctCode === '' || $correctCode === $wrongCode) {
            throw new RuntimeException('The AI generated an exercise without two different variants.');
        }

        $errors = $this->errors($response['errors'], substr_count($wrongCode, "\n") + 1);

        if ($errors === []) {
            throw new RuntimeException('The AI generated an exercise without an explained error.');
        }

        return DB::transaction(function () use ($submission, $topics, $response, $correctCode, $wrongCode, $errors): Exercise {
            $exercise = $submission->exercises()->create([
                'topic_id' => $topics[$this->string($response['topic'])] ?? reset($topics),
                'title' => Str::limit($this->string($response['question']), 255, ''),
                'stars' => self::STARS,
                'correct_code' => $correctCode,
                'wrong_code' => $wrongCode,
                // Exercises about a student's own code are only for that student.
                'published_at' => null,
            ]);
            $exercise->errors()->createMany($errors);

            return $exercise;
        });
    }

    /**
     * Store the student's answer as an attempt and let the AI assess the clicked lines and explanation.
     * The choice is the variant the student thinks is wrong.
     *
     * @param list<int> $lines the lines the student clicked in that variant
     */
    public function answer(Exercise $exercise, User $user, Variant $choice, array $lines, string $explanation): Attempt
    {
        $lines = array_values(array_unique($lines));
        sort($lines);

        $attempt = new Attempt([
            'number' => 1,
            'chosen_side' => $exercise->side($choice),
            'selected_lines' => $lines,
            'explanation' => $explanation,
        ]);
        $attempt->exercise()->associate($exercise);
        $attempt->user()->associate($user);

        $assessor = new AnswerAssessor($attempt);
        $response = $assessor->prompt($assessor->toPrompt());

        $score = $response['score'];
        $score = max(0, min(10, is_numeric($score) ? (int) $score : 0));
        $attempt->stars = max(0, (int) round($score / 10 * $exercise->stars));
        $attempt->feedback = $this->string($response['feedback']);

        return DB::transaction(function () use ($exercise, $user, $attempt): Attempt {
            // Two tabs can both pass the "not answered yet" check during the slow AI call; only the first one may store its answer.
            Exercise::query()->whereKey($exercise->getKey())->lockForUpdate()->first();

            if ($exercise->attempts()->whereBelongsTo($user)->exists()) {
                throw new ExerciseAlreadyAnswered();
            }

            $attempt->save();
            $exercise->users()->syncWithoutDetaching([$user->id => [
                'status' => ProgressStatus::Done,
                'best_stars' => $attempt->stars,
                'completed_at' => now(),
            ]]);
            $user->increment('stars', $attempt->stars);

            return $attempt;
        });
    }

    /**
     * Store the student's reaction and the tutor's reply.
     */
    public function reply(Attempt $attempt, string $content): AttemptMessage
    {
        // Only store the conversation once the AI replied, so a failed call leaves no unanswered message behind.
        $response = (new ExerciseTutor($attempt))->prompt(ExerciseTutor::studentMessage($content));

        return DB::transaction(function () use ($attempt, $content, $response): AttemptMessage {
            $attempt->messages()->create(['role' => AttemptMessageRole::Student, 'content' => $content]);

            return $attempt->messages()->create(['role' => AttemptMessageRole::Tutor, 'content' => trim($response->text)]);
        });
    }

    /**
     * The errors the AI pointed out, with line numbers kept within the wrong code.
     *
     * @return list<array{line: int, title: string, hint: string, explanation: string}>
     */
    private function errors(mixed $errors, int $lineCount): array
    {
        $valid = [];

        foreach (is_array($errors) ? $errors : [] as $error) {
            if (!is_array($error) || $this->string($error['explanation'] ?? null) === '') {
                continue;
            }

            $line = $error['line'] ?? null;
            $valid[] = [
                'line' => max(1, min($lineCount, is_numeric($line) ? (int) $line : 1)),
                'title' => Str::limit($this->string($error['title'] ?? null), 255, ''),
                'hint' => $this->string($error['hint'] ?? null),
                'explanation' => $this->string($error['explanation'] ?? null),
            ];
        }

        return $valid;
    }

    /**
     * Snippets taken from a class come back with the first line unindented and the
     * rest indented one level deeper; remove that extra indentation.
     */
    private function code(mixed $value): string
    {
        $lines = explode("\n", str_replace("\r\n", "\n", rtrim(is_scalar($value) ? (string) $value : '')));
        $lines[0] = ltrim($lines[0]);

        $indents = array_map(
            fn (string $line): int => strlen($line) - strlen(ltrim($line, ' ')),
            array_filter(array_slice($lines, 1), fn (string $line): bool => trim($line) !== ''),
        );
        $indent = $indents === [] ? 0 : min($indents);

        foreach (array_slice($lines, 1, preserve_keys: true) as $index => $line) {
            $lines[$index] = substr($line, min($indent, strlen($line)));
        }

        return trim(implode("\n", $lines), "\n");
    }

    private function string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
