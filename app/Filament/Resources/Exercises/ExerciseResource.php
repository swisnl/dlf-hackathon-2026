<?php

namespace App\Filament\Resources\Exercises;

use App\Filament\Resources\Exercises\Pages\CreateExercise;
use App\Filament\Resources\Exercises\Pages\EditExercise;
use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Filament\Resources\Exercises\Schemas\ExerciseForm;
use App\Filament\Resources\Exercises\Tables\ExercisesTable;
use App\Filament\Resources\Resource;
use App\Models\Exercise;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

class ExerciseResource extends Resource
{
    protected static ?string $model = Exercise::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Bug;

    /**
     * @param \App\Models\Exercise&\Illuminate\Database\Eloquent\Model $record
     */
    #[Override]
    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->title;
    }

    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'title',
        ];
    }

    /**
     * Every exercise comes with its number of errors and attempts.
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['errors', 'attempts']);
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ExerciseForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ExercisesTable::configure($table)
            ->defaultSort('title');
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListExercises::route('/'),
            'create' => CreateExercise::route('/create'),
            'edit' => EditExercise::route('/{record}/edit'),
        ];
    }
}
