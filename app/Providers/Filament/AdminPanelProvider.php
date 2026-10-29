<?php

namespace App\Providers\Filament;

use App\Enums\NavigationGroup;
use App\Enums\UserRole;
use App\Models\User;
use Filament\Navigation\NavigationGroup as FilamentNavigationGroup;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Override;

/**
 * User management, for administrators only.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $this->configure($panel, 'admin', 'admin')
            ->navigationGroups([
                FilamentNavigationGroup::make(NavigationGroup::Settings->getLabel())
                    ->collapsible(false),
            ]);
    }

    #[Override]
    protected function developerLoginUsers(): Builder
    {
        return User::query()->where('role', UserRole::Admin);
    }
}
