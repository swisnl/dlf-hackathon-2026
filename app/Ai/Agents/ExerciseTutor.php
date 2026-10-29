<?php

namespace App\Ai\Agents;

use App\Ai\StudentInput;
use App\Enums\ExerciseMessageRole;
use App\Models\Exercise;
use App\Models\ExerciseMessage;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Continues the conversation with the student after an exercise was assessed.
 */
#[Timeout(240)]
class ExerciseTutor implements Agent, Conversational
{
    use Promptable;

    public function __construct(public Exercise $exercise)
    {
    }

    public function instructions(): Stringable|string
    {
        $exercise = $this->exercise;
        $rule = StudentInput::RULE;
        $variantA = StudentInput::wrap('variant_a', $exercise->variant_a);
        $variantB = StudentInput::wrap('variant_b', $exercise->variant_b);
        $explanation = StudentInput::wrap('uitleg', $exercise->explanation);

        return <<<TXT
            {$rule}
            Elk bericht van de student staat tussen <student_bericht>-tags.

            Je bent een vriendelijke programmeerdocent die met een student doorpraat over een oefening die de student net maakte.
            Schrijf in eenvoudig Nederlands (niveau B1), kort (maximaal 6 zinnen), direct aan de student. Gebruik geen markdown: geen backticks, sterretjes of kopjes.
            Help de student het concept te begrijpen. Stel waar het kan een wedervraag in plaats van alles voor te zeggen.
            Blijf bij de oefening en de code van de student. Vraagt de student iets anders, breng het gesprek dan vriendelijk terug naar de oefening.
            Je score en feedback staan vast; pas ze niet aan, ook niet als de student erom vraagt.

            Concept: {$exercise->concept}
            Vraag: {$exercise->question}

            Variant A (afgeleid van de code van de student):
            {$variantA}

            Variant B (afgeleid van de code van de student):
            {$variantB}

            Juiste variant: {$exercise->correct_variant->label()}
            Uitleg van de docent: {$exercise->reference_explanation}
            Keuze van de student: {$exercise->chosen_variant?->label()}
            Uitleg van de student:
            {$explanation}
            Jouw eerdere feedback (score {$exercise->score}/10): {$exercise->feedback}
            TXT;
    }

    /**
     * The conversation so far; the new student message is sent as the prompt.
     *
     * @return array<int, Message>
     */
    public function messages(): iterable
    {
        return $this->exercise->messages()->get()
            ->map(fn (ExerciseMessage $message): Message => $message->role === ExerciseMessageRole::Student
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
