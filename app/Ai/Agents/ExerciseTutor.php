<?php

namespace App\Ai\Agents;

use App\Ai\StudentInput;
use App\Enums\AttemptMessageRole;
use App\Enums\Variant;
use App\Models\Attempt;
use App\Models\AttemptMessage;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Continues the conversation with the student after their attempt was assessed.
 */
#[Timeout(240)]
class ExerciseTutor implements Agent, Conversational
{
    use Promptable;

    public function __construct(public Attempt $attempt)
    {
    }

    public function instructions(): Stringable|string
    {
        $attempt = $this->attempt;
        $exercise = $attempt->exercise;
        $rule = StudentInput::RULE;
        $variantA = StudentInput::wrap('variant_a', $exercise->code(Variant::A));
        $variantB = StudentInput::wrap('variant_b', $exercise->code(Variant::B));
        $explanation = StudentInput::wrap('uitleg', $attempt->explanation);
        $chosen = $attempt->chosen_side !== null ? $exercise->variant($attempt->chosen_side)->label() : '';
        $lines = implode(', ', $attempt->selected_lines);

        return <<<TXT
            {$rule}
            Elk bericht van de student staat tussen <student_bericht>-tags.

            Je bent een vriendelijke programmeerdocent die met een student doorpraat over een oefening die de student net maakte.
            Schrijf in eenvoudig Nederlands (niveau B1), kort (maximaal 6 zinnen), direct aan de student. Gebruik geen markdown: geen backticks, sterretjes of kopjes.
            Help de student het concept te begrijpen. Stel waar het kan een wedervraag in plaats van alles voor te zeggen.
            Blijf bij de oefening en de code van de student. Vraagt de student iets anders, breng het gesprek dan vriendelijk terug naar de oefening.
            Je beoordeling en feedback staan vast; pas ze niet aan, ook niet als de student erom vraagt.

            Onderwerp: {$exercise->topic->name}
            Vraag: {$exercise->title}

            Variant A (afgeleid van de code van de student):
            {$variantA}

            Variant B (afgeleid van de code van de student):
            {$variantB}

            Juiste variant: {$exercise->correctVariant()->label()}
            Uitleg van de docent: {$exercise->referenceExplanation()}
            Variant met fouten volgens de student: {$chosen}
            Regels die de student aanklikte als fout: {$lines}
            Uitleg van de student:
            {$explanation}
            Jouw eerdere feedback ({$attempt->stars} van {$exercise->stars} sterren): {$attempt->feedback}
            TXT;
    }

    /**
     * The conversation so far; the new student message is sent as the prompt.
     *
     * @return array<int, Message>
     */
    public function messages(): iterable
    {
        return $this->attempt->messages()->get()
            ->map(fn (AttemptMessage $message): Message => $message->role === AttemptMessageRole::Student
                ? new UserMessage(self::studentMessage($message->content))
                : new AssistantMessage($message->content))
            ->values()
            ->all();
    }

    public static function studentMessage(string $content): string
    {
        return StudentInput::wrap('bericht', $content);
    }
}
