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
#[Timeout(240)]
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
            - Maak twee varianten van een klein stuk van die code (maximaal 20 regels per variant).
            - Eén variant is juist. De andere variant bevat één duidelijke, inhoudelijke fout die past bij het concept (geen typfout of opmaakverschil).
            - Houd beide varianten even lang en in dezelfde stijl, zodat het verschil niet meteen opvalt.

            Regels voor de velden:
            - question: één korte vraag, zonder code en zonder het antwoord te verklappen. Bijvoorbeeld: "Welke versie van de methode total() rekent het totaal goed uit?"
            - correct_code en incorrect_code: alleen de code zelf, zonder uitleg, kopjes, labels of markdown-codeblokken.
            - reference_explanation: maximaal 4 korte zinnen over waarom de juiste versie juist is en wat er mis is met de foute versie. Noem de versies "de juiste versie" en "de foute versie", nooit A, B, optie 1 of optie 2: de volgorde wordt later gehusseld. De student ziet deze uitleg pas na het antwoorden.
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
