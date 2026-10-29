<?php

namespace App\Ai\Agents;

use App\Models\Exercise;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Assesses the student's choice and, above all, their explanation of why.
 */
#[Timeout(120)]
class AnswerAssessor implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(public Exercise $exercise)
    {
    }

    public function instructions(): Stringable|string
    {
        return <<<'TXT'
            Je bent een vriendelijke programmeerdocent. Een student koos bij een oefening een variant en legde uit waarom.
            Beoordeel vooral de uitleg: begrijpt de student het concept? Een goede keuze met een slechte uitleg is maar half goed.
            Een foute keuze met een uitleg die laat zien dat de student nadenkt, verdient ook waardering.

            Schrijf de feedback direct aan de student (je/jij), in eenvoudig Nederlands (niveau B1), in maximaal 5 korte zinnen.
            Noem wat goed was, wat beter kan, en eindig met één vraag die de student verder laat nadenken.
            Geef een score van 0 tot en met 10 voor keuze en uitleg samen.
            TXT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'score' => $schema->integer()->min(0)->max(10)->description('Score voor keuze en uitleg samen.')->required(),
            'feedback' => $schema->string()->description('Feedback aan de student.')->required(),
        ];
    }

    public function toPrompt(): string
    {
        $exercise = $this->exercise;
        $chosen = $exercise->chosen_variant;
        $correct = $exercise->correct_variant;

        return <<<TXT
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

            Juiste variant: {$correct->label()}
            Uitleg van de docent: {$exercise->reference_explanation}

            Keuze van de student: {$chosen?->label()}
            Uitleg van de student: {$exercise->explanation}
            TXT;
    }
}
