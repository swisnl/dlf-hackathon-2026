<?php

namespace App\Providers;

use App\Session\DatabaseSessionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
    }
}
