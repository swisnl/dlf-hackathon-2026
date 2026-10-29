<?php

namespace App\Providers;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

/**
 * Component defaults shared by all Filament panels.
 */
class FilamentServiceProvider extends ServiceProvider
{
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
