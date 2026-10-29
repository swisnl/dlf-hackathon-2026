<?php

namespace App\Highlighting;

use Phiki\Grammar\Grammar;
use Phiki\Phiki;
use Phiki\Token\HighlightedToken;

/**
 * Splits PHP code into lines of coloured pieces in the Eigenwijs colours, so a page can render
 * every line on its own without parsing HTML.
 */
final class CodeHighlighter
{
    private const string THEME = 'eigenwijs';

    private readonly Phiki $phiki;

    public function __construct()
    {
        $this->phiki = (new Phiki())->theme(self::THEME, resource_path('highlighting/eigenwijs.json'));
    }

    /**
     * @return list<list<array{text: string, color: string|null}>> one entry per line
     */
    public function lines(string $code): array
    {
        /** @var array<int, array<HighlightedToken>> $lines */
        $lines = $this->phiki->codeToHighlightedTokens(str_replace("\r\n", "\n", $code), Grammar::Php, self::THEME);

        return array_values(array_map($this->pieces(...), $lines));
    }

    /**
     * Neighbouring tokens with the same colour become one piece, which keeps the page props small.
     *
     * @param array<HighlightedToken> $tokens
     *
     * @return list<array{text: string, color: string|null}>
     */
    private function pieces(array $tokens): array
    {
        $pieces = [];

        foreach ($tokens as $token) {
            $text = rtrim($token->token->text, "\n");
            $color = $token->settings['default']->foreground ?? null;

            if ($text === '') {
                continue;
            }

            $last = array_key_last($pieces);

            if ($last !== null && $pieces[$last]['color'] === $color) {
                $pieces[$last]['text'] .= $text;
            } else {
                $pieces[] = ['text' => $text, 'color' => $color];
            }
        }

        return $pieces;
    }
}
