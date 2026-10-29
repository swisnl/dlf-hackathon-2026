<?php

namespace App\Enums;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProgressStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Retry = 'retry';
    case Done = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => __('enum.progress_status.new'),
            self::Retry => __('enum.progress_status.retry'),
            self::Done => __('enum.progress_status.done'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::Retry => 'warning',
            self::Done => 'success',
        };
    }

    public function getIcon(): LucideIcon
    {
        return match ($this) {
            self::New => LucideIcon::Sparkles,
            self::Retry => LucideIcon::RotateCcw,
            self::Done => LucideIcon::CircleCheck,
        };
    }
}
