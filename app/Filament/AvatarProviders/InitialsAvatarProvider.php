<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Draws the initials locally, so no name or email address goes to an avatar service.
 */
class InitialsAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $initials = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => str($segment)->replaceMatches('/^[^\p{L}\p{N}]+/u', '')->substr(0, 1)->toString())
            ->filter()
            ->take(2)
            ->join('');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="#7e22ce"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#fff" font-family="sans-serif" font-size="26">'.e(mb_strtoupper($initials)).'</text>'
            .'</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
