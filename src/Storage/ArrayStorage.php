<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Storage;

use Reformtech\ShortUrl\Contracts\StorageInterface;

/**
 * In-memory storage. Good for tests, demos, or single-request CLI scripts.
 * Data does NOT persist between requests.
 */
class ArrayStorage implements StorageInterface
{
    /** @var array<string, array{url: string, clicks: int, expires_at: ?\DateTimeInterface}> */
    private array $records = [];

    public function save(string $code, string $originalUrl, ?\DateTimeInterface $expiresAt = null): void
    {
        $this->records[$code] = [
            'url' => $originalUrl,
            'clicks' => 0,
            'expires_at' => $expiresAt,
        ];
    }

    public function find(string $code): ?string
    {
        $record = $this->records[$code] ?? null;

        if ($record === null) {
            return null;
        }

        if ($record['expires_at'] !== null && $record['expires_at'] < new \DateTimeImmutable()) {
            return null;
        }

        return $record['url'];
    }

    public function exists(string $code): bool
    {
        return isset($this->records[$code]);
    }

    public function delete(string $code): bool
    {
        if (! isset($this->records[$code])) {
            return false;
        }

        unset($this->records[$code]);

        return true;
    }

    public function incrementClicks(string $code): void
    {
        if (isset($this->records[$code])) {
            $this->records[$code]['clicks']++;
        }
    }
}
