<?php

namespace App\Providers;

use App\Ai\ClaudeCode\ClaudeCodeProvider;
use App\Session\DatabaseSessionHandler;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\AiManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Runs prompts through Claude Code on the developer's Mac, see bin/claude-bridge.mjs.
        $this->callAfterResolving(AiManager::class, function (AiManager $ai): void {
            $ai->extend('claude-code', fn (Application $app, array $config): ClaudeCodeProvider => new ClaudeCodeProvider($config, $app->make(Dispatcher::class)));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());

        // Replaces Laravel's database session driver, so SESSION_DRIVER=database stores no IP address or user agent.
        Session::extend('database', function (Application $app): DatabaseSessionHandler {
            $connection = config('session.connection');

            return new DatabaseSessionHandler(
                $app->make(DatabaseManager::class)->connection(is_string($connection) ? $connection : null),
                config()->string('session.table'),
                config()->integer('session.lifetime'),
                $app,
            );
        });

        Relation::enforceMorphMap([
        ]);

        // Every AI call costs money and time, so limit how often a student can trigger one.
        RateLimiter::for('ai', fn (Request $request) => Limit::perMinute(20)->by($request->user()?->getAuthIdentifier() ?? $request->ip()));
    }
}
