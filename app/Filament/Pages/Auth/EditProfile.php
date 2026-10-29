<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Infolists\Components\Timestamp;
use App\Filament\Schemas\RecordLayout;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Override;

/**
 * @method \Filament\Forms\Components\TextInput getNameFormComponent()
 * @method \Filament\Forms\Components\TextInput getEmailFormComponent()
 * @method \Filament\Forms\Components\TextInput getPasswordFormComponent()
 * @method \Filament\Forms\Components\TextInput getPasswordConfirmationFormComponent()
 * @method \Filament\Forms\Components\TextInput getCurrentPasswordFormComponent()
 * @method \App\Models\User                     getUser()
 */
class EditProfile extends BaseEditProfile
{
    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                RecordLayout::make(
                    Section::make()
                        ->inlineLabel(false)
                        ->schema([
                            $this->getNameFormComponent(),
                            $this->getEmailFormComponent(),
                            Grid::make()
                                ->columnSpanFull()
                                ->schema([
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent(),
                                    $this->getCurrentPasswordFormComponent()
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->columns(2),
                    Section::make()
                        ->inlineLabel(false)
                        ->schema([
                            TextEntry::make('role')
                                ->label(__('resource.user.fields.role'))
                                ->badge(),
                            ...Timestamp::lifecycle(),
                        ]),
                ),
            ]);
    }
}
