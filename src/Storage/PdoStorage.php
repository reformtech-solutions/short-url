<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Storage;

use PDO;
use Reformtech\ShortUrl\Contracts\StorageInterface;

/**
 * Plain PHP storage backed by PDO. Works with MySQL, Postgres, SQLite — anything PDO supports.
 *
 * Expects a table (default name "short_urls") with columns:
 *   code VARCHAR(255) UNIQUE, original_url TEXT, clicks INT DEFAULT 0, expires_at DATETIME NULL
 *
 * See database/schema.sql in this package for a ready-made CREATE TABLE statement.
 */
class PdoStorage implements StorageInterface
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'short_urls',
    ) {
    }

    public function save(string $code, string $originalUrl, ?\DateTimeInterface $expiresAt = null): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (code, original_url, clicks, expires_at, created_at)
             VALUES (:code, :original_url, 0, :expires_at, :created_at)"
        );

        $stmt->execute([
            'code' => $code,
            'original_url' => $originalUrl,
            'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function find(string $code): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT original_url, expires_at FROM {$this->table} WHERE code = :code LIMIT 1"
        );
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        if ($row['expires_at'] !== null && new \DateTimeImmutable($row['expires_at']) < new \DateTimeImmutable()) {
            return null;
        }

        return $row['original_url'];
    }

    public function exists(string $code): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table} WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);

        return $stmt->fetchColumn() !== false;
    }

    public function delete(string $code): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE code = :code");
        $stmt->execute(['code' => $code]);

        return $stmt->rowCount() > 0;
    }

    public function incrementClicks(string $code): void
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET clicks = clicks + 1 WHERE code = :code");
        $stmt->execute(['code' => $code]);
    }
}
