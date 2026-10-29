<?php

namespace App\Filament\Infolists\Components;

use Carbon\CarbonInterface;
use Filament\Infolists\Components\TextEntry;
use Override;

class Timestamp extends TextEntry
{
    /**
     * The created, updated and deleted timestamps every record shows in its aside.
     *
     * @return list<static>
     */
    public static function lifecycle(): array
    {
        return [
            static::make('created_at'),
            static::make('updated_at'),
            static::make('deleted_at'),
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('resource.fields.'.$this->getName()));
        $this->dateTime('j F Y - H:i');
        $this->hint(fn (?CarbonInterface $state): ?string => $state?->diffForHumans());
        $this->hidden(fn (?CarbonInterface $state): bool => !$state instanceof CarbonInterface);
    }
}
