<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case Settings = 'settings';

    public function getLabel(): string
    {
        return match ($this) {
            self::Settings => __('enum.navigation_group.settings'),
        };
    }
}
