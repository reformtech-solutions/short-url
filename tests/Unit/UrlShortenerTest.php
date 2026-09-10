<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Reformtech\ShortUrl\Exceptions\DuplicateCodeException;
use Reformtech\ShortUrl\Exceptions\InvalidUrlException;
use Reformtech\ShortUrl\Exceptions\ShortUrlNotFoundException;
use Reformtech\ShortUrl\Storage\ArrayStorage;
use Reformtech\ShortUrl\UrlShortener;

class UrlShortenerTest extends TestCase
{
    private UrlShortener $shortener;

    protected function setUp(): void
    {
        // No Laravel, no database — pure PHP.
        $this->shortener = new UrlShortener(new ArrayStorage());
    }

    public function test_it_shortens_and_resolves_a_url(): void
    {
        $code = $this->shortener->shorten('https://example.com/some/long/path');

        $this->assertNotEmpty($code);
        $this->assertSame('https://example.com/some/long/path', $this->shortener->resolve($code));
    }

    public function test_it_accepts_a_custom_code(): void
    {
        $code = $this->shortener->shorten('https://example.com/sale', 'summer-sale');

        $this->assertSame('summer-sale', $code);
        $this->assertSame('https://example.com/sale', $this->shortener->resolve('summer-sale'));
    }

    public function test_it_rejects_a_duplicate_custom_code(): void
    {
        $this->shortener->shorten('https://example.com/a', 'same-code');

        $this->expectException(DuplicateCodeException::class);
        $this->shortener->shorten('https://example.com/b', 'same-code');
    }

    public function test_it_rejects_an_invalid_url(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->shortener->shorten('not-a-real-url');
    }

    public function test_it_throws_when_resolving_an_unknown_code(): void
    {
        $this->expectException(ShortUrlNotFoundException::class);
        $this->shortener->resolve('does-not-exist');
    }

    public function test_it_forgets_a_code(): void
    {
        $code = $this->shortener->shorten('https://example.com/temp');

        $this->assertTrue($this->shortener->forget($code));
        $this->assertFalse($this->shortener->exists($code));
    }
}
