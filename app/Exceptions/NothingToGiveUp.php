<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Giving up is only possible after a first attempt that was not flawless.
 */
final class NothingToGiveUp extends RuntimeException
{
}
