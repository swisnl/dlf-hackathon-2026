<?php

namespace App\Ai\Agents;

use App\Models\Submission;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Generates a quiz exercise about a student's own code: two variants of a
 * piece of that code, one correct and one with a subtle mistake.
 */
#[Timeout(120)]
class ExerciseGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param list<string> $previousConcepts
     */
    public function __construct(
        public Submission $submission,
        public array $previousConcepts = [],
    ) {
    }

    public function instructions(): Stringable|string
    {
        return <<<'TXT'
            Je bent een programmeerdocent die een student overhoort over de code die de student zelf schreef.
            De studenten hebben een laag taalniveau: schrijf in eenvoudig Nederlands (niveau B1), met korte zinnen en zo weinig mogelijk jargon.

            Maak één oefening:
            - Kies één concept dat echt in de code van de student voorkomt (bijvoorbeeld een class, een functie, een loop, validatie).
            - Maak twee varianten van een klein stuk van die code (maximaal ongeveer 30 regels per variant).
            - Eén variant is juist. De andere variant bevat één duidelijke, inhoudelijke fout die past bij het concept (geen typfout of opmaakverschil).
            - Houd beide varianten even lang en in dezelfde stijl, zodat het verschil niet meteen opvalt.
            - De vraag vraagt de student welke variant juist is. Verklap het antwoord niet in de vraag.
            - Leg in reference_explanation kort uit waarom de juiste variant juist is en wat er mis is met de andere. Deze uitleg ziet de student pas na het antwoorden.
            TXT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'concept' => $schema->string()->description('Het concept waar de oefening over gaat, in een paar woorden.')->required(),
            'question' => $schema->string()->description('De vraag aan de student.')->required(),
            'correct_code' => $schema->string()->description('De juiste variant van de code.')->required(),
            'incorrect_code' => $schema->string()->description('De variant met één inhoudelijke fout.')->required(),
            'reference_explanation' => $schema->string()->description('Waarom de juiste variant juist is en wat er mis is met de andere.')->required(),
        ];
    }

    public function toPrompt(): string
    {
        $prompt = "Bestand: {$this->submission->filename}\nTitel: {$this->submission->title}\n\nCode van de student:\n```\n{$this->submission->code}\n```";

        if ($this->previousConcepts !== []) {
            $prompt .= "\n\nKies een ander concept dan deze eerdere oefeningen: ".implode(', ', $this->previousConcepts).'.';
        }

        return $prompt;
    }
}
