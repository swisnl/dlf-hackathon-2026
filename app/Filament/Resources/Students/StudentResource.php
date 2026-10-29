<?php

namespace App\Filament\Resources\Students;

use App\Filament\Resources\Resource;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\AttemptsRelationManager;
use App\Filament\Resources\Students\Schemas\StudentInfolist;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::GraduationCap;

    /**
     * @param \App\Models\Student&\Illuminate\Database\Eloquent\Model $record
     */
    #[Override]
    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->name;
    }

    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'email',
        ];
    }

    /**
     * Every student comes with their badge count and the number of exercises they have attempted.
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'badges',
                'attempts as exercises_count' => fn (Builder $query) => $query->select(DB::raw('count(distinct exercise_id)')),
            ]);
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return StudentInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table)
            ->defaultSort('name');
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            AttemptsRelationManager::class,
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'view' => ViewStudent::route('/{record}'),
        ];
    }
}
