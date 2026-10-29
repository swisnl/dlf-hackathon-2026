<?php

namespace App\Filament\Resources\Exercises\Actions;

use App\Models\Exercise;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Override;

/**
 * Shows the saved exercise the way a student sees it: both classes side by side, with the errors marked.
 */
class PreviewExerciseAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'preview';
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('resource.exercise.actions.preview'));
        $this->icon(LucideIcon::Eye);
        $this->color('gray');
        $this->modalHeading(fn (Exercise $record): string => $record->title);
        $this->modalWidth('7xl');
        $this->modalContent(fn (Exercise $record): View => view('filament.exercises.preview', [
            'exercise' => $record->load('errors'),
        ]));
        $this->modalSubmitAction(false);
        $this->modalCancelActionLabel(__('resource.exercise.actions.close'));
    }
}
