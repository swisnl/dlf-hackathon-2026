<?php

namespace App\Providers\Filament;

use App\Models\User;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * The application itself, for students and teachers.
 */
class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->configure($panel, 'app', '')
            ->default()
            ->registration();
    }

    #[Override]
    protected function developerLoginUsers(): Builder
    {
        return User::query();
    }
}
