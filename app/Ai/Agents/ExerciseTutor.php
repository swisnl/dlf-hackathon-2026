<?php

namespace App\Ai\Agents;

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
#[Timeout(120)]
class ExerciseTutor implements Agent, Conversational
{
    use Promptable;

    public function __construct(public Exercise $exercise)
    {
    }

    public function instructions(): Stringable|string
    {
        $exercise = $this->exercise;

        return <<<TXT
            Je bent een vriendelijke programmeerdocent die met een student doorpraat over een oefening die de student net maakte.
            Schrijf in eenvoudig Nederlands (niveau B1), kort (maximaal 6 zinnen), direct aan de student.
            Help de student het concept te begrijpen. Stel waar het kan een wedervraag in plaats van alles voor te zeggen.
            Blijf bij de oefening en de code van de student.

            Concept: {$exercise->concept}
            Vraag: {$exercise->question}

            Variant A:
            ```
            {$exercise->variant_a}
            ```

            Variant B:
            ```
            {$exercise->variant_b}
            ```

            Juiste variant: {$exercise->correct_variant->label()}
            Uitleg van de docent: {$exercise->reference_explanation}
            Keuze van de student: {$exercise->chosen_variant?->label()}
            Uitleg van de student: {$exercise->explanation}
            Jouw eerdere feedback (score {$exercise->score}/10): {$exercise->feedback}
            TXT;
    }

    /**
     * The conversation so far, without the newest student message (that is the prompt).
     *
     * @return array<int, Message>
     */
    public function messages(): iterable
    {
        $messages = $this->exercise->messages()->get();

        if ($messages->last()?->role === ExerciseMessageRole::Student) {
            $messages->pop();
        }

        return $messages
            ->map(fn (ExerciseMessage $message): Message => $message->role === ExerciseMessageRole::Student
                ? new UserMessage($message->content)
                : new AssistantMessage($message->content))
            ->values()
            ->all();
    }
}
