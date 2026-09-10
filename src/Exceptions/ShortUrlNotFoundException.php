<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Exceptions;

class ShortUrlNotFoundException extends \RuntimeException
{
    public static function forCode(string $code): self
    {
        return new self("No URL was found for the short code [{$code}].");
    }
}
