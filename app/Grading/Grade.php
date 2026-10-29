<?php

namespace App\Grading;

use App\Enums\CodeSide;

/**
 * The outcome of one attempt: which clicked lines were errors, which were not, the stars that earns
 * and whether it completes the exercise (flawless, or the last attempt).
 */
final readonly class Grade
{
    /**
     * The highest attempt number; the last attempt earns at most half of the stars.
     */
    public const int MAX_ATTEMPTS = 2;

    /**
     * @param list<int> $selectedLines the clicked lines, each once and in order
     */
    private function __construct(
        public array $selectedLines,
        public int $found,
        public int $wrong,
        public int $stars,
        public bool $flawless,
        public bool $completes,
    ) {
    }

    /**
     * Lines in the correct class are never errors, so clicking them always counts as wrong.
     *
     * @param list<int> $errorLines    the lines of the wrong class that contain an error, at least one
     * @param array<int> $selectedLines the lines the student clicked
     */
    public static function calculate(int $exerciseStars, array $errorLines, CodeSide $side, array $selectedLines, int $attemptNumber): self
    {
        $selectedLines = array_unique($selectedLines);
        sort($selectedLines);
        $found = $side === CodeSide::Wrong ? count(array_intersect($selectedLines, $errorLines)) : 0;
        $wrong = count($selectedLines) - $found;
        $flawless = $found === count($errorLines) && $wrong === 0;

        return new self(
            selectedLines: $selectedLines,
            found: $found,
            wrong: $wrong,
            stars: self::stars($exerciseStars, count($errorLines), max(0, $found - $wrong), $attemptNumber),
            flawless: $flawless,
            completes: $flawless || $attemptNumber === self::MAX_ATTEMPTS,
        );
    }

    /**
     * exercise stars × net found ÷ errors, halved on the last attempt, rounded half up.
     *
     * Calculated on integers so 0.5 always rounds up instead of depending on float precision.
     */
    private static function stars(int $exerciseStars, int $errorCount, int $netFound, int $attemptNumber): int
    {
        $numerator = $exerciseStars * $netFound;
        $denominator = $errorCount * ($attemptNumber === self::MAX_ATTEMPTS ? 2 : 1);

        return intdiv(2 * $numerator + $denominator, 2 * $denominator);
    }
}
