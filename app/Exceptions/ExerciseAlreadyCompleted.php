<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A completed exercise (flawless, after the last attempt or after giving up) earns no more stars.
 */
final class ExerciseAlreadyCompleted extends RuntimeException
{
}
