<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CodeSide: string implements HasLabel
{
    case Correct = 'correct';
    case Wrong = 'wrong';

    public function getLabel(): string
    {
        return match ($this) {
            self::Correct => __('enum.code_side.correct'),
            self::Wrong => __('enum.code_side.wrong'),
        };
    }
}
