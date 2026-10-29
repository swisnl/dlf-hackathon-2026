<?php

namespace App\Enums;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasIcon, HasLabel
{
    case Student = 'student';
    case Teacher = 'teacher';
    case Admin = 'admin';

    /**
     * Whether this role may use the Filament admin panel.
     */
    public function hasPanelAccess(): bool
    {
        return match ($this) {
            self::Student => false,
            self::Teacher, self::Admin => true,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Student => __('enum.user_role.student'),
            self::Teacher => __('enum.user_role.teacher'),
            self::Admin => __('enum.user_role.admin'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Student => 'gray',
            self::Teacher => 'primary',
            self::Admin => 'info',
        };
    }

    public function getIcon(): LucideIcon
    {
        return match ($this) {
            self::Student => LucideIcon::GraduationCap,
            self::Teacher => LucideIcon::Presentation,
            self::Admin => LucideIcon::ShieldUser,
        };
    }
}
