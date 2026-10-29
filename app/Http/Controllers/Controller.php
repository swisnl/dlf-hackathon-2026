<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Ai\Exceptions\ProviderConnectionException;
use Throwable;

abstract class Controller
{
    /**
     * Seconds a request that waits for the AI may run: well above the agents' timeout of 240 seconds,
     * so a slow call ends in our own error message instead of a fatal error.
     */
    protected const int AI_TIME_LIMIT = 300;

    protected function allowSlowAi(): void
    {
        set_time_limit(self::AI_TIME_LIMIT);
    }

    /**
     * Report a failed AI call and get the message to show the student.
     */
    protected function aiFailed(Throwable $e, string $message): string
    {
        report($e);

        return $e instanceof ProviderConnectionException
            ? 'De AI deed er te lang over of was niet bereikbaar. '.$message
            : $message;
    }

    protected function user(Request $request): User
    {
        $user = $request->user();
        assert($user instanceof User);

        return $user;
    }
}
