<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Contracts;

/**
 * Any storage backend (array, PDO, Eloquent, Redis, etc.) must implement this.
 * This is the only thing that keeps the core decoupled from any framework.
 */
interface StorageInterface
{
    /**
     * Persist a new short code -> original URL mapping.
     * Should throw Reformtech\ShortUrl\Exceptions\DuplicateCodeException
     * if the code already exists.
     */
    public function save(string $code, string $originalUrl, ?\DateTimeInterface $expiresAt = null): void;

    /**
     * Find the original URL for a given code.
     * Returns null if the code doesn't exist (caller decides how to handle "not found").
     */
    public function find(string $code): ?string;

    /**
     * Whether a code is already taken.
     */
    public function exists(string $code): bool;

    /**
     * Delete a mapping. Returns true if something was deleted.
     */
    public function delete(string $code): bool;

    /**
     * Increment the click/hit counter for a code (no-op is fine if not supported).
     */
    public function incrementClicks(string $code): void;
}
