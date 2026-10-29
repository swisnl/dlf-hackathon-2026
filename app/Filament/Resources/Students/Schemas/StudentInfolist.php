<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Infolists\Components\Timestamp;
use App\Filament\Schemas\RecordLayout;
use App\Models\Student;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RecordLayout::make(
                    Section::make()
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('resource.student.fields.name')),
                            TextEntry::make('email')
                                ->label(__('resource.student.fields.email')),
                            TextEntry::make('badges.name')
                                ->label(__('resource.student.fields.badges'))
                                ->badge()
                                ->icon(fn (string $state, Student $record): ?string => $record->badges->firstWhere('name', $state)?->icon)
                                ->color(fn (string $state, Student $record): ?array => ($color = $record->badges->firstWhere('name', $state)?->color) !== null ? Color::hex($color) : null)
                                ->placeholder(__('resource.student.placeholders.badges'))
                                ->columnSpanFull(),
                        ])->columns(2),
                    Section::make()
                        ->schema([
                            TextEntry::make('stars')
                                ->label(__('resource.student.fields.stars'))
                                ->icon(LucideIcon::Star)
                                ->iconColor('warning'),
                            TextEntry::make('exercises_count')
                                ->label(__('resource.student.fields.exercises_count')),
                            ...Timestamp::lifecycle(),
                        ]),
                ),
            ]);
    }
}
