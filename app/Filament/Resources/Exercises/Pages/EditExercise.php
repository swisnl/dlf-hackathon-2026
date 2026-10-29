<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\Actions\PreviewExerciseAction;
use App\Filament\Resources\Exercises\ExerciseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditExercise extends EditRecord
{
    protected static string $resource = ExerciseResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            PreviewExerciseAction::make(),
            DeleteAction::make(),
        ];
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
