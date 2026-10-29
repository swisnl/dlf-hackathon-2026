<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource as BaseResource;
use Illuminate\Support\Str;
use Override;

abstract class Resource extends BaseResource
{
    #[Override]
    public static function getModelLabel(): string
    {
        return __(static::getResourceTranslationKey().'.singular');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __(static::getResourceTranslationKey().'.plural');
    }

    protected static function getResourceTranslationKey(): string
    {
        return 'resource.'.Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->snake();
    }
}
