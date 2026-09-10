<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel;

use Illuminate\Support\ServiceProvider;
use Reformtech\ShortUrl\CodeGenerator;
use Reformtech\ShortUrl\UrlShortener;

class UrlShortenerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/short-url.php', 'short-url');

        // Bound as a singleton — powers the simple ShortUrl::shorten()/resolve() facade helpers.
        $this->app->singleton(UrlShortener::class, function ($app) {
            $config = $app['config']->get('short-url');

            return new UrlShortener(
                storage: new EloquentStorage(),
                codeGenerator: new CodeGenerator($config['key_length'] ?? 5),
            );
        });

        // Bound fresh each time — the Builder is stateful per short URL being created.
        $this->app->bind(Builder::class, fn () => new Builder());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/short-url.php' => config_path('short-url.php'),
        ], 'short-url-config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if (! config('short-url.disable_default_route', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }
    }
}
