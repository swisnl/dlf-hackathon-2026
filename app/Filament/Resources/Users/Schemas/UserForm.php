<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Filament\Infolists\Components\Timestamp;
use App\Filament\Schemas\RecordLayout;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RecordLayout::make(
                    Section::make()
                        ->schema([
                            TextInput::make('name')
                                ->label(__('resource.user.fields.name'))
                                ->autocomplete(false)
                                ->maxLength(255)
                                ->required(),
                            TextInput::make('email')
                                ->label(__('resource.user.fields.email'))
                                ->maxLength(255)
                                ->unique()
                                ->email()
                                ->required(),
                            TextInput::make('password')
                                ->label(__('resource.user.fields.password'))
                                ->password()
                                ->revealable()
                                ->autocomplete('new-password')
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->helperText(fn (string $operation) => $operation === 'edit' ? __('resource.user.helpers.password') : null),
                        ])->columns(2),
                    Section::make()
                        ->schema([
                            Select::make('role')
                                ->label(__('resource.user.fields.role'))
                                ->options(UserRole::class)
                                ->default(UserRole::Student)
                                ->required(),
                            ...Timestamp::lifecycle(),
                        ]),
                ),
            ]);
    }
}
