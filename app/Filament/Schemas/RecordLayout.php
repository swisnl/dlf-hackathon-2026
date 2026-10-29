<?php

declare(strict_types=1);

namespace App\Filament\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class RecordLayout
{
    public static function make(Section $main, Section $aside): Grid
    {
        return Grid::make([
            'default' => 1,
            'lg' => 4,
        ])
            ->schema([
                $main->columnSpan(['lg' => 3]),
                $aside->columnSpan(['lg' => 1]),
            ])
            ->columnSpanFull();
    }
}
