<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Student = 'student';
    case Admin = 'admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Student => __('enum.user_role.student'),
            self::Admin => __('enum.user_role.admin'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Student => 'gray',
            self::Admin => 'info',
        };
    }
}
