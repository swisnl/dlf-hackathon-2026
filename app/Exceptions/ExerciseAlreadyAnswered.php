<?php

namespace App\Exceptions;

use RuntimeException;

class ExerciseAlreadyAnswered extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This exercise has already been answered.');
    }
}
