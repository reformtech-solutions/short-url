<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl;

use Reformtech\ShortUrl\Contracts\StorageInterface;
use Reformtech\ShortUrl\Exceptions\DuplicateCodeException;
use Reformtech\ShortUrl\Exceptions\InvalidUrlException;
use Reformtech\ShortUrl\Exceptions\ShortUrlNotFoundException;

/**
 * Framework-agnostic core. Works in plain PHP, Laravel, Symfony, anything —
 * it only ever talks to StorageInterface, never to a framework.
 *
 * Plain PHP usage:
 *   $shortener = new UrlShortener(new PdoStorage($pdo));
 *   $code = $shortener->shorten('https://example.com/very/long/url');
 *
 * Laravel usage: see Laravel\Facades\ShortUrl, which wraps this class.
 */
class UrlShortener
{
    public function __construct(
        private StorageInterface $storage,
        private CodeGenerator $codeGenerator = new CodeGenerator(),
    ) {
    }

    /**
     * Shorten a URL and return the generated (or custom) short code.
     *
     * @param string $originalUrl The long URL to shorten.
     * @param string|null $customCode Developer-supplied code (e.g. "summer-sale").
     *                                If null, a random code is generated.
     * @param \DateTimeInterface|null $expiresAt Optional expiry.
     *
     * @throws InvalidUrlException if $originalUrl isn't a valid URL.
     * @throws DuplicateCodeException if $customCode is already taken.
     */
    public function shorten(
        string $originalUrl,
        ?string $customCode = null,
        ?\DateTimeInterface $expiresAt = null,
    ): string {
        $this->assertValidUrl($originalUrl);

        $code = $customCode ?? $this->generateUniqueCode();

        if ($this->storage->exists($code)) {
            throw DuplicateCodeException::forCode($code);
        }

        $this->storage->save($code, $originalUrl, $expiresAt);

        return $code;
    }

    /**
     * Resolve a short code back to its original URL.
     *
     * @throws ShortUrlNotFoundException if the code doesn't exist.
     */
    public function resolve(string $code, bool $trackClick = true): string
    {
        $originalUrl = $this->storage->find($code);

        if ($originalUrl === null) {
            throw ShortUrlNotFoundException::forCode($code);
        }

        if ($trackClick) {
            $this->storage->incrementClicks($code);
        }

        return $originalUrl;
    }

    /**
     * Delete a short URL mapping. Returns true if something was removed.
     */
    public function forget(string $code): bool
    {
        return $this->storage->delete($code);
    }

    public function exists(string $code): bool
    {
        return $this->storage->exists($code);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = $this->codeGenerator->generate();
        } while ($this->storage->exists($code));

        return $code;
    }

    private function assertValidUrl(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw InvalidUrlException::forUrl($url);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw InvalidUrlException::forUrl($url);
        }
    }
}
