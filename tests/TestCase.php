<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Reformtech\ShortUrl\Laravel\Facades\ShortUrl;
use Reformtech\ShortUrl\Laravel\UrlShortenerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [UrlShortenerServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['ShortUrl' => ShortUrl::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
