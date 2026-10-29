<?php

namespace App\Ai\Agents;

use App\Ai\StudentInput;
use App\Enums\Variant;
use App\Models\Exercise;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Wijsje, the owl in the corner: explains programming concepts in very plain language, without giving away answers.
 */
#[Timeout(240)]
class ConceptExplainer implements Agent, Conversational
{
    use Promptable;

    /**
     * @param list<array{role: 'student'|'wijsje', content: string}> $history the conversation so far, kept in the browser only
     */
    public function __construct(
        public array $history = [],
        public ?Exercise $exercise = null,
    ) {
    }

    public function instructions(): Stringable|string
    {
        $rule = StudentInput::RULE;

        return <<<TXT
            {$rule}
            Elk bericht van de student staat tussen <student_vraag>-tags.

            Je bent Wijsje, de uil van Eigenwijs. Je legt programmeren en Laravel uit aan mbo-studenten die moeite hebben met moeilijke woorden.
            Schrijf in heel eenvoudig Nederlands (niveau A2): korte zinnen en gewone woorden. Houd het kort: maximaal 4 zinnen in één alinea. Sluit af met één korte, vriendelijke vraag die de student uitnodigt om door te vragen, bijvoorbeeld of het duidelijk is of dat je een voorbeeld zal geven.
            Gebruik je toch een vakwoord, leg het dan meteen uit. Geef waar het helpt een voorbeeld uit het dagelijks leven.
            Gebruik geen markdown: geen backticks, sterretjes of kopjes. Schrijf code-namen gewoon in de tekst.
            Praat alleen over programmeren en Laravel. Vraagt de student iets anders, zeg dan vriendelijk waar je wel mee helpt.
            Verklap nooit het antwoord van een oefening: zeg niet welke versie klopt of welke regel fout is. Leg wel uit wat een begrip betekent, zodat de student het zelf kan vinden.
            {$this->exerciseContext()}
            TXT;
    }

    /**
     * The conversation so far; the new question is sent as the prompt.
     *
     * @return array<int, Message>
     */
    public function messages(): iterable
    {
        return array_map(fn (array $message): Message => $message['role'] === 'student'
            ? new UserMessage(self::question($message['content']))
            : new AssistantMessage($message['content']), $this->history);
    }

    public static function question(string $content): string
    {
        return StudentInput::wrap('vraag', $content);
    }

    /**
     * What the student sees on the exercise page; never which version is correct or what is wrong with it.
     */
    private function exerciseContext(): string
    {
        if (!$this->exercise instanceof Exercise) {
            return '';
        }

        $exercise = $this->exercise;
        $variantA = StudentInput::wrap('variant_a', $exercise->code(Variant::A));
        $variantB = StudentInput::wrap('variant_b', $exercise->code(Variant::B));

        return <<<TXT

            De student kijkt nu naar deze oefening. Eén van de twee versies klopt; dat weet jij niet en daar ga je ook niet naar raden.
            Onderwerp: {$exercise->topic->name}
            Vraag: {$exercise->title}

            Versie A:
            {$variantA}

            Versie B:
            {$variantB}
            TXT;
    }
}
