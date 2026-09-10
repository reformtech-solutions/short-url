<?php

use Illuminate\Support\Facades\Route;
use Reformtech\ShortUrl\Laravel\Http\Controllers\RedirectController;

if (! config('short-url.disable_default_route', false)) {
    $prefix = config('short-url.prefix');
    $path = $prefix ? '/' . trim($prefix, '/') . '/{shortUrlKey}' : '/{shortUrlKey}';

    Route::get($path, RedirectController::class)
        ->middleware(config('short-url.middleware', []))
        ->name('short-url.redirect');
}
