<?php

namespace App\Filament\Resources\Students\RelationManagers;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * The attempts of a student, grouped per exercise, with the lines they clicked and the stars they earned.
 */
class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    #[Override]
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resource.attempt.plural');
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->defaultGroup(
                Group::make('exercise.title')
                    ->label(__('resource.attempt.fields.exercise'))
                    ->titlePrefixedWithLabel(false),
            )
            ->defaultSort('number')
            ->columns([
                TextColumn::make('number')
                    ->label(__('resource.attempt.fields.number')),
                TextColumn::make('selected_lines')
                    ->label(__('resource.attempt.fields.selected_lines'))
                    ->badge()
                    ->color('gray')
                    ->placeholder('–'),
                TextColumn::make('found')
                    ->label(__('resource.attempt.fields.found'))
                    ->numeric(),
                TextColumn::make('wrong')
                    ->label(__('resource.attempt.fields.wrong'))
                    ->numeric(),
                TextColumn::make('stars')
                    ->label(__('resource.attempt.fields.stars'))
                    ->icon(LucideIcon::Star)
                    ->iconColor('warning'),
                IconColumn::make('gave_up')
                    ->label(__('resource.attempt.fields.gave_up'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('resource.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
