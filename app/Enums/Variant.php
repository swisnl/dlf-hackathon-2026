<?php

namespace App\Enums;

enum Variant: string
{
    case A = 'a';
    case B = 'b';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Variant A',
            self::B => 'Variant B',
        };
    }
}
