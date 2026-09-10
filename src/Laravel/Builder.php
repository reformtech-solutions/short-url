<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel;

use Illuminate\Support\Traits\Conditionable;
use Reformtech\ShortUrl\CodeGenerator;
use Reformtech\ShortUrl\Exceptions\DuplicateCodeException;
use Reformtech\ShortUrl\Exceptions\InvalidUrlException;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;

/**
 * Fluent builder for creating short URLs in Laravel with advanced
 * features (single-use, tracking, activation windows, etc.) that
 * go beyond what the framework-agnostic core supports.
 *
 * Usage:
 *   ShortUrl::destinationUrl('https://example.com')
 *       ->urlKey('summer-sale')
 *       ->singleUse()
 *       ->make();
 */
class Builder
{
    use Conditionable;

    private ?string $destinationUrl = null;

    private ?string $urlKey = null;

    private bool $singleUse = false;

    private bool $secure = false;

    private ?int $redirectStatusCode = null;

    private ?bool $trackVisits = null;

    private ?\DateTimeInterface $activateAt = null;

    private ?\DateTimeInterface $deactivateAt = null;

    /** @var array<int, \Closure(ShortUrl): void> */
    private array $beforeCreateCallbacks = [];

    public function __construct(private CodeGenerator $codeGenerator = new CodeGenerator())
    {
    }

    public function destinationUrl(string $url): self
    {
        $this->destinationUrl = $url;

        return $this;
    }

    /**
     * Set a custom, human-readable key instead of a randomly generated one.
     */
    public function urlKey(?string $key): self
    {
        $this->urlKey = $key;

        return $this;
    }

    public function singleUse(bool $singleUse = true): self
    {
        $this->singleUse = $singleUse;

        return $this;
    }

    /**
     * Force the destination URL to be visited over HTTPS.
     */
    public function secure(bool $secure = true): self
    {
        $this->secure = $secure;

        return $this;
    }

    public function redirectStatusCode(int $code): self
    {
        $this->redirectStatusCode = $code;

        return $this;
    }

    public function trackVisits(bool $track = true): self
    {
        $this->trackVisits = $track;

        return $this;
    }

    public function activateAt(\DateTimeInterface $date): self
    {
        $this->activateAt = $date;

        return $this;
    }

    public function deactivateAt(\DateTimeInterface $date): self
    {
        $this->deactivateAt = $date;

        return $this;
    }

    /**
     * Run a callback against the ShortUrl model just before it's saved —
     * useful for attaching your own custom columns (e.g. tenant_id).
     *
     * @param \Closure(ShortUrl): void $callback
     */
    public function beforeCreate(\Closure $callback): self
    {
        $this->beforeCreateCallbacks[] = $callback;

        return $this;
    }

    /**
     * Build and persist the short URL, returning the ShortUrl model.
     *
     * @throws InvalidUrlException
     * @throws DuplicateCodeException
     */
    public function make(): ShortUrl
    {
        $destinationUrl = $this->destinationUrl
            ?? throw new \LogicException('A destination URL must be set via destinationUrl() before calling make().');

        $this->assertValidUrl($destinationUrl);

        if ($this->secure) {
            $destinationUrl = preg_replace('#^http://#i', 'https://', $destinationUrl);
        }

        $key = $this->urlKey ?? $this->generateUniqueKey();

        if (ShortUrl::query()->where('url_key', $key)->exists()) {
            throw DuplicateCodeException::forCode($key);
        }

        $model = new ShortUrl([
            'url_key' => $key,
            'destination_url' => $destinationUrl,
            'single_use' => $this->singleUse,
            'secure' => $this->secure,
            'redirect_status_code' => $this->redirectStatusCode
                ?? (int) config('short-url.redirect_status_code', 302),
            'track_visits' => $this->trackVisits
                ?? (bool) config('short-url.tracking.default_enabled', true),
            'activate_at' => $this->activateAt,
            'deactivate_at' => $this->deactivateAt,
        ]);

        foreach ($this->beforeCreateCallbacks as $callback) {
            $callback($model);
        }

        $model->save();

        return $model;
    }

    private function generateUniqueKey(): string
    {
        $length = (int) config('short-url.key_length', 5);
        $this->codeGenerator = new CodeGenerator(max(3, $length));

        do {
            $key = $this->codeGenerator->generate();
        } while (ShortUrl::query()->where('url_key', $key)->exists());

        return $key;
    }

    private function assertValidUrl(string $url): void
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw InvalidUrlException::forUrl($url);
        }

        $allowedSchemes = (array) config('short-url.allowed_url_schemes', ['http://', 'https://']);

        $matchesScheme = array_reduce(
            $allowedSchemes,
            fn (bool $carry, string $scheme) => $carry || str_starts_with($url, $scheme),
            false,
        );

        if (! $matchesScheme) {
            throw InvalidUrlException::forUrl($url);
        }
    }
}
