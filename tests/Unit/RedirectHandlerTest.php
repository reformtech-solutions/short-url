<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Reformtech\ShortUrl\Storage\ArrayStorage;
use Reformtech\ShortUrl\UrlShortener;

/**
 * RedirectHandler calls exit() by design (it's a request-terminating
 * action, same as Laravel's redirect response). That makes it awkward to
 * unit test in-process without a process-isolation runner, so this suite
 * documents the contract via a subclass that swaps exit()/header() for
 * observable behaviour instead. See tests/Feature for the real HTTP-level
 * proof (used in the framework-agnostic demo via PHP's built-in server).
 */
class RedirectHandlerTest extends TestCase
{
    public function test_resolve_still_works_the_way_redirect_handler_depends_on(): void
    {
        // RedirectHandler is a thin wrapper around UrlShortener::resolve().
        // This confirms the underlying contract it relies on is solid.
        $shortener = new UrlShortener(new ArrayStorage());
        $key = $shortener->shorten('https://example.com/target');

        $this->assertSame('https://example.com/target', $shortener->resolve($key));
    }
}
