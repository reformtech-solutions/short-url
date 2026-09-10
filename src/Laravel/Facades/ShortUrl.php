<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Reformtech\ShortUrl\Laravel\Builder;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl as ShortUrlModel;
use Reformtech\ShortUrl\UrlShortener;

/**
 * @method static Builder destinationUrl(string $url)
 * @method static Builder urlKey(?string $key)
 * @method static Builder singleUse(bool $singleUse = true)
 * @method static Builder secure(bool $secure = true)
 * @method static Builder redirectStatusCode(int $code)
 * @method static Builder trackVisits(bool $track = true)
 * @method static Builder activateAt(\DateTimeInterface $date)
 * @method static Builder deactivateAt(\DateTimeInterface $date)
 * @method static Builder beforeCreate(\Closure $callback)
 * @method static \Reformtech\ShortUrl\Laravel\Models\ShortUrl make()
 *
 * @see Builder
 */
class ShortUrl extends Facade
{
    /**
     * Quick one-liner: shorten a URL and get back just the key.
     * For advanced features (single-use, tracking, activation windows),
     * use the fluent builder methods instead, e.g.
     * ShortUrl::destinationUrl($url)->singleUse()->make().
     */
    public static function shorten(string $originalUrl, ?string $customKey = null): string
    {
        return app(Builder::class)
            ->destinationUrl($originalUrl)
            ->urlKey($customKey)
            ->make()
            ->url_key;
    }

    /**
     * Quick one-liner: resolve a key back to its destination URL.
     */
    public static function resolve(string $key): string
    {
        return app(UrlShortener::class)->resolve($key);
    }

    public static function forget(string $key): bool
    {
        return ShortUrlModel::query()->where('url_key', $key)->delete() > 0;
    }

    protected static function getFacadeAccessor(): string
    {
        return Builder::class;
    }
}
