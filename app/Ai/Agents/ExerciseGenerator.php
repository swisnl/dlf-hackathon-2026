<?php

namespace App\Ai\Agents;

use App\Ai\StudentInput;
use App\Models\Submission;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Generates a quiz exercise about a student's own code: two versions of a
 * piece of that code, one correct and one with a subtle mistake.
 */
#[Timeout(240)]
class ExerciseGenerator implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * @param list<string> $topics           names of the topics the exercise can belong to
     * @param list<string> $previousConcepts
     */
    public function __construct(
        public Submission $submission,
        public array $topics,
        public array $previousConcepts = [],
    ) {
    }

    public function instructions(): Stringable|string
    {
        return StudentInput::RULE."\n\n".<<<'TXT'
            Je bent een programmeerdocent die een student overhoort over de code die de student zelf schreef.
            De studenten hebben een laag taalniveau: schrijf in eenvoudig Nederlands (niveau B1), met korte zinnen en zo weinig mogelijk jargon.

            Maak één oefening:
            - Kies één concept dat echt in de code van de student voorkomt (bijvoorbeeld een class, een functie, een loop, validatie).
            - Maak twee versies van een klein stuk van die code (maximaal 20 regels per versie).
            - Eén versie is juist. De andere versie bevat één duidelijke, inhoudelijke fout die past bij het concept (geen typfout of opmaakverschil).
            - Houd beide versies even lang en in dezelfde stijl, zodat het verschil niet meteen opvalt.

            Regels voor de velden:
            - topic: het onderwerp uit de lijst dat het best bij het concept past.
            - question: één korte vraag, zonder code en zonder het antwoord te verklappen. Bijvoorbeeld: "Welke versie van de methode total() rekent het totaal goed uit?"
            - correct_code en incorrect_code: alleen de code zelf, zonder uitleg, kopjes, labels of markdown-codeblokken.
            - errors: de fout(en) in incorrect_code, meestal één. Per fout:
              - line: het regelnummer in incorrect_code (de eerste regel is 1);
              - title: wat er mis is, in een paar woorden;
              - hint: een vraag die de student op weg helpt, zonder het antwoord te geven;
              - explanation: maximaal 3 korte zinnen over waarom het fout is en hoe het goed moet.
              Noem de versies "de juiste versie" en "de foute versie", nooit A, B, optie 1 of optie 2: de volgorde wordt later gehusseld. De student ziet de uitleg pas na het antwoorden.
            TXT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'topic' => $schema->string()->enum($this->topics)->description('Het onderwerp van de oefening.')->required(),
            'question' => $schema->string()->description('De vraag aan de student.')->required(),
            'correct_code' => $schema->string()->description('De juiste versie van de code.')->required(),
            'incorrect_code' => $schema->string()->description('De versie met één inhoudelijke fout.')->required(),
            'errors' => $schema->array()->min(1)->max(3)->items($schema->object([
                'line' => $schema->integer()->min(1)->description('Regelnummer van de fout in incorrect_code.')->required(),
                'title' => $schema->string()->description('Wat er mis is, in een paar woorden.')->required(),
                'hint' => $schema->string()->description('Een vraag die de student op weg helpt.')->required(),
                'explanation' => $schema->string()->description('Waarom het fout is en hoe het goed moet.')->required(),
            ]))->description('De fouten in de foute versie.')->required(),
        ];
    }

    public function toPrompt(): string
    {
        $prompt = 'Titel en bestand van de student:'
            ."\n".StudentInput::wrap('titel', "{$this->submission->title} ({$this->submission->filename})")
            ."\n\nCode van de student:\n".StudentInput::wrap('code', $this->submission->code)
            ."\n\nKies het onderwerp uit: ".implode(', ', $this->topics).'.';

        if ($this->previousConcepts !== []) {
            $prompt .= "\n\nKies een ander concept dan deze eerdere oefeningen: ".implode(', ', $this->previousConcepts).'.';
        }

        return $prompt;
    }
}
