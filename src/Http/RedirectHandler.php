<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Http;

use Reformtech\ShortUrl\Exceptions\ShortUrlNotFoundException;
use Reformtech\ShortUrl\UrlShortener;

/**
 * Handles the full "read the short code from the request, resolve it,
 * send the redirect" flow for plain PHP projects — the same job Laravel's
 * RedirectController does automatically, but framework-free.
 *
 * Usage in your entry script (e.g. index.php):
 *
 *   $shortener = require __DIR__ . '/bootstrap.php';
 *   (new RedirectHandler($shortener))->handle();
 *
 * That's it — no manual $_GET reading, header() calls, or try/catch needed.
 */
class RedirectHandler
{
    public function __construct(
        private UrlShortener $shortener,
        private string $queryParam = 's',
        private int $redirectStatus = 302,
    ) {
    }

    /**
     * Reads the short code from $_GET, resolves it, and sends the redirect.
     * Sends a 400 if the code is missing, or a 404 if it doesn't resolve.
     * Terminates the script (calls exit) once the response is sent.
     */
    public function handle(): never
    {
        $code = $_GET[$this->queryParam] ?? null;

        if ($code === null || $code === '') {
            http_response_code(400);
            echo 'Missing short URL code.';
            exit;
        }

        try {
            $originalUrl = $this->shortener->resolve($code);
        } catch (ShortUrlNotFoundException) {
            http_response_code(404);
            echo 'Short URL not found.';
            exit;
        }

        header("Location: {$originalUrl}", true, $this->redirectStatus);
        exit;
    }
}
