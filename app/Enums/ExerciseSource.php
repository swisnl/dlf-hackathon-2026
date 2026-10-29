<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Who made the exercise: a teacher in the backoffice, or the AI about the student's own code.
 */
enum ExerciseSource: string implements HasLabel
{
    case Teacher = 'teacher';
    case Ai = 'ai';

    public function getLabel(): string
    {
        return match ($this) {
            self::Teacher => __('enum.exercise_source.teacher'),
            self::Ai => __('enum.exercise_source.ai'),
        };
    }
}
