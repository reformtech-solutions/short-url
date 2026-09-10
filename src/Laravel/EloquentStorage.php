<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel;

use Reformtech\ShortUrl\Contracts\StorageInterface;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;

/**
 * Bridges the framework-agnostic core to Laravel's Eloquent ORM.
 *
 * This adapter powers the *simple* facade one-liners (ShortUrl::shorten(),
 * ShortUrl::resolve()). It reads/writes the same `short_urls` table used
 * by the fluent Builder, but only concerns itself with the columns the
 * generic StorageInterface contract knows about (url_key, destination_url,
 * clicks). Advanced per-link behaviour (single-use, activation windows,
 * tracking) is handled separately by the Builder and RedirectController,
 * since that logic is Laravel-specific and outside the agnostic core.
 */
class EloquentStorage implements StorageInterface
{
    public function save(string $code, string $originalUrl, ?\DateTimeInterface $expiresAt = null): void
    {
        ShortUrl::query()->create([
            'url_key' => $code,
            'destination_url' => $originalUrl,
            'clicks' => 0,
            'deactivate_at' => $expiresAt,
        ]);
    }

    public function find(string $code): ?string
    {
        $record = ShortUrl::query()
            ->where('url_key', $code)
            ->where(function ($query) {
                $query->whereNull('deactivate_at')->orWhere('deactivate_at', '>', now());
            })
            ->first();

        return $record?->destination_url;
    }

    public function exists(string $code): bool
    {
        return ShortUrl::query()->where('url_key', $code)->exists();
    }

    public function delete(string $code): bool
    {
        return ShortUrl::query()->where('url_key', $code)->delete() > 0;
    }

    public function incrementClicks(string $code): void
    {
        ShortUrl::query()->where('url_key', $code)->increment('clicks');
    }
}
