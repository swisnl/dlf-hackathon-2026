<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Filament\Infolists\Components\Timestamp;
use App\Filament\Schemas\RecordLayout;
use App\Models\Exercise;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make(__('resource.exercise.warnings.attempted.heading'))
                    ->description(__('resource.exercise.warnings.attempted.description'))
                    ->warning()
                    ->visible(fn (?Exercise $record): bool => $record?->attempts()->exists() ?? false)
                    ->columnSpanFull(),
                RecordLayout::make(
                    Section::make()
                        ->schema([
                            TextInput::make('title')
                                ->label(__('resource.exercise.fields.title'))
                                ->maxLength(255)
                                ->required()
                                ->columnSpanFull(),
                            CodeEditor::make('correct_code')
                                ->label(__('resource.exercise.fields.correct_code'))
                                ->language(Language::Php)
                                ->required(),
                            CodeEditor::make('wrong_code')
                                ->label(__('resource.exercise.fields.wrong_code'))
                                ->language(Language::Php)
                                ->live(onBlur: true)
                                ->required(),
                            Repeater::make('errors')
                                ->label(__('resource.exercise.fields.errors'))
                                ->relationship()
                                ->minItems(1)
                                ->defaultItems(1)
                                ->reorderable(false)
                                ->collapsible()
                                ->schema([
                                    TextInput::make('line')
                                        ->label(__('resource.exercise.fields.line'))
                                        ->helperText(__('resource.exercise.helpers.line'))
                                        ->integer()
                                        ->minValue(1)
                                        ->maxValue(fn (Get $get): int => count(explode("\n", $get->string('../../wrong_code'))))
                                        ->distinct()
                                        ->required(),
                                    TextInput::make('title')
                                        ->label(__('resource.exercise.fields.error_title'))
                                        ->maxLength(255)
                                        ->required()
                                        ->columnSpan(3),
                                    Textarea::make('hint')
                                        ->label(__('resource.exercise.fields.hint'))
                                        ->rows(2)
                                        ->required()
                                        ->columnSpan(2),
                                    Textarea::make('explanation')
                                        ->label(__('resource.exercise.fields.explanation'))
                                        ->rows(2)
                                        ->required()
                                        ->columnSpan(2),
                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ])->columns(2),
                    Section::make()
                        ->schema([
                            Toggle::make('published_at')
                                ->label(__('resource.exercise.fields.published'))
                                ->helperText(__('resource.exercise.helpers.published'))
                                ->formatStateUsing(fn (mixed $state): bool => filled($state))
                                ->dehydrateStateUsing(fn (bool $state, ?Exercise $record): ?Carbon => $state ? ($record->published_at ?? now()) : null),
                            Select::make('topic_id')
                                ->label(__('resource.exercise.fields.topic'))
                                ->relationship('topic', 'name')
                                ->preload()
                                ->required(),
                            ToggleButtons::make('stars')
                                ->label(__('resource.exercise.fields.stars'))
                                ->options([1 => '1', 2 => '2', 3 => '3'])
                                ->inline()
                                ->default(1)
                                ->required(),
                            ...Timestamp::lifecycle(),
                        ]),
                ),
            ]);
    }
}
