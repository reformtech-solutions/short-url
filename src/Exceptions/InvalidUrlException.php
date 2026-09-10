<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Exceptions;

class InvalidUrlException extends \InvalidArgumentException
{
    public static function forUrl(string $url): self
    {
        return new self("The URL [{$url}] is not a valid, well-formed URL.");
    }
}
