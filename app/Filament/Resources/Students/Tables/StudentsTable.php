<?php

namespace App\Filament\Resources\Students\Tables;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resource.student.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('resource.student.fields.email'))
                    ->searchable(),
                TextColumn::make('stars')
                    ->label(__('resource.student.fields.stars'))
                    ->icon(LucideIcon::Star)
                    ->iconColor('warning')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('badges_count')
                    ->label(__('resource.student.fields.badges_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('exercises_count')
                    ->label(__('resource.student.fields.exercises_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('resource.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
