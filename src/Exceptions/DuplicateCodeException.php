<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Exceptions;

class DuplicateCodeException extends \RuntimeException
{
    public static function forCode(string $code): self
    {
        return new self("The short code [{$code}] is already in use.");
    }
}
