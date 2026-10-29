<?php

namespace App\Providers\Filament;

use App\Enums\UserRole;
use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Awcodes\Gravatar\GravatarPlugin;
use Awcodes\Gravatar\GravatarProvider;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Cocosmos\FilamentStickySaveBar\Enums\ShowOn;
use Cocosmos\FilamentStickySaveBar\StickySaveBarPlugin;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->unsavedChangesAlerts()
            ->defaultAvatarProvider(GravatarProvider::class)
            ->spa()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->passwordReset()
            ->maxContentWidth('full')
            ->sidebarWidth('fit-content')
            ->navigationGroups([
                NavigationGroup::make(\App\Enums\NavigationGroup::Settings->getLabel())
                    ->collapsible(false),
            ])
            ->profile(EditProfile::class, false)
            ->colors([
                'primary' => Color::Purple,
                'secondary' => Color::Neutral,
                'success' => Color::Green,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'info' => Color::Teal,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
                    ->users(fn () => User::query()->where('role', UserRole::Admin)->pluck('email', 'name')->toArray()),
                GravatarPlugin::make(),
                LightSwitchPlugin::make(),
                StickySaveBarPlugin::make()
                    ->showOn(ShowOn::DirtyAlways),
            ]);
    }

    public function boot(): void
    {
        FilamentColor::register(Color::all());

        Select::configureUsing(static fn (Select $select): Select => $select->native(false));
        SelectFilter::configureUsing(static fn (SelectFilter $selectFilter): SelectFilter => $selectFilter->native(false));
        Table::configureUsing(static fn (Table $table): Table => $table
            ->stackedOnMobile()
            ->deferFilters(false)
            ->deferColumnManager(false)
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25));
        CreateAction::configureUsing(static fn (CreateAction $createAction): CreateAction => $createAction->icon(LucideIcon::SquarePlus));
        ViewAction::configureUsing(static fn (ViewAction $viewAction): ViewAction => $viewAction->icon(LucideIcon::Eye));
        EditAction::configureUsing(static fn (EditAction $editAction): EditAction => $editAction->icon(LucideIcon::SquarePen));
        DeleteAction::configureUsing(static fn (DeleteAction $deleteAction): DeleteAction => $deleteAction->icon(LucideIcon::Trash2));
    }
}
