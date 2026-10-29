<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Awcodes\Gravatar\GravatarPlugin;
use Awcodes\Gravatar\GravatarProvider;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Cocosmos\FilamentStickySaveBar\Enums\ShowOn;
use Cocosmos\FilamentStickySaveBar\StickySaveBarPlugin;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider as BasePanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

/**
 * Shared look, authentication and plugins for every panel.
 */
abstract class PanelProvider extends BasePanelProvider
{
    /**
     * The users offered as one-click logins in local development.
     *
     * @return Builder<User>
     */
    abstract protected function developerLoginUsers(): Builder;

    protected function configure(Panel $panel, string $id, string $path): Panel
    {
        $directory = ucfirst($id);

        return $panel
            ->id($id)
            ->path($path)
            ->unsavedChangesAlerts()
            ->defaultAvatarProvider(GravatarProvider::class)
            ->spa()
            ->viteTheme('resources/css/filament/theme.css')
            ->login()
            ->passwordReset()
            ->maxContentWidth('full')
            ->sidebarWidth('fit-content')
            ->profile(EditProfile::class, false)
            ->colors([
                'primary' => Color::Purple,
                'secondary' => Color::Neutral,
                'success' => Color::Green,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'info' => Color::Teal,
            ])
            ->discoverResources(in: app_path("Filament/{$directory}/Resources"), for: "App\\Filament\\{$directory}\\Resources")
            ->discoverPages(in: app_path("Filament/{$directory}/Pages"), for: "App\\Filament\\{$directory}\\Pages")
            ->discoverWidgets(in: app_path("Filament/{$directory}/Widgets"), for: "App\\Filament\\{$directory}\\Widgets")
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ], isPersistent: true)
            ->plugins([
                EnvironmentIndicatorPlugin::make()
                    ->color(static fn (): array => match (app()->environment()) {
                        'production' => Color::Red,
                        'staging' => Color::Orange,
                        default => Color::Amber,
                    })
                    ->showGitBranch(),
                FilamentBackgroundsPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->switchable(false)
                    ->users(fn () => $this->developerLoginUsers()->pluck('email', 'name')->toArray()),
                GravatarPlugin::make(),
                LightSwitchPlugin::make(),
                StickySaveBarPlugin::make()
                    ->showOn(ShowOn::DirtyAlways),
            ]);
    }
}
