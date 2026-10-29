<?php

namespace App\Filament\Resources\Exercises\Tables;

use App\Filament\Resources\Exercises\Actions\PreviewExerciseAction;
use App\Models\Exercise;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExercisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('resource.exercise.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('topic.name')
                    ->label(__('resource.exercise.fields.topic'))
                    ->badge()
                    ->color(fn (Exercise $record): ?array => $record->topic ? Color::hex($record->topic->color) : null)
                    ->sortable(),
                TextColumn::make('stars')
                    ->label(__('resource.exercise.fields.stars'))
                    ->icon(LucideIcon::Star)
                    ->iconColor('warning')
                    ->sortable(),
                TextColumn::make('errors_count')
                    ->label(__('resource.exercise.fields.errors'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('attempts_count')
                    ->label(__('resource.exercise.fields.attempts_count'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('published_at')
                    ->label(__('resource.exercise.fields.published'))
                    ->state(fn (Exercise $record): bool => $record->published_at !== null)
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('resource.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('topic')
                    ->label(__('resource.exercise.fields.topic'))
                    ->relationship('topic', 'name')
                    ->preload(),
                TernaryFilter::make('published_at')
                    ->label(__('resource.exercise.fields.published'))
                    ->nullable(),
            ])
            ->recordActions([
                PreviewExerciseAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
