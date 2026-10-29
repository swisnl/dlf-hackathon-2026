<?php

namespace Tests\Unit\Grading;

use App\Enums\CodeSide;
use App\Grading\Grade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GradeTest extends TestCase
{
    private const ERRORS = [4, 8, 12];

    /**
     * The cases from the issue: an exercise of 3 stars with 3 errors.
     *
     * @param list<int> $selectedLines
     */
    #[DataProvider('cases')]
    public function test_it_calculates_the_stars(CodeSide $side, array $selectedLines, int $attempt, int $found, int $wrong, int $stars): void
    {
        $grade = Grade::calculate(3, self::ERRORS, $side, $selectedLines, $attempt);

        $this->assertSame([$found, $wrong, $stars], [$grade->found, $grade->wrong, $grade->stars]);
    }

    /**
     * @return array<string, array{CodeSide, list<int>, int, int, int, int}>
     */
    public static function cases(): array
    {
        return [
            'attempt 1, all found' => [CodeSide::Wrong, [4, 8, 12], 1, 3, 0, 3],
            'attempt 1, 2 of 3' => [CodeSide::Wrong, [4, 8], 1, 2, 0, 2],
            'attempt 1, 2 of 3 plus 1 wrong' => [CodeSide::Wrong, [4, 8, 5], 1, 2, 1, 1],
            'wrong class' => [CodeSide::Correct, [4, 8, 12], 1, 0, 3, 0],
            'attempt 2, all found' => [CodeSide::Wrong, [4, 8, 12], 2, 3, 0, 2],
            'attempt 2, 2 of 3' => [CodeSide::Wrong, [4, 8], 2, 2, 0, 1],
            'more wrong than found' => [CodeSide::Wrong, [4, 1, 2], 1, 1, 2, 0],
            'nothing selected' => [CodeSide::Wrong, [], 1, 0, 0, 0],
            'a line clicked twice counts once' => [CodeSide::Wrong, [4, 4, 8, 12], 1, 3, 0, 3],
        ];
    }

    public function test_only_finding_every_error_without_mistakes_is_flawless(): void
    {
        $this->assertTrue(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4, 8, 12], 1)->flawless);
        $this->assertFalse(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4, 8], 1)->flawless);
        $this->assertFalse(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4, 8, 12, 1], 1)->flawless);
    }

    public function test_a_flawless_or_last_attempt_completes_the_exercise(): void
    {
        $this->assertTrue(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4, 8, 12], 1)->completes);
        $this->assertFalse(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4, 8], 1)->completes);
        $this->assertTrue(Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [4], 2)->completes);
    }

    public function test_the_selection_is_stored_once_and_in_order(): void
    {
        $this->assertSame([4, 8, 12], Grade::calculate(3, self::ERRORS, CodeSide::Wrong, [5 => 12, 2 => 4, 9 => 8, 3 => 4], 1)->selectedLines);
    }

    public function test_a_half_star_rounds_up(): void
    {
        // 1 star × 1 of 2 errors = 0.5 → 1
        $this->assertSame(1, Grade::calculate(1, [3, 6], CodeSide::Wrong, [3], 1)->stars);
    }
}
