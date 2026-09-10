<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl;

class CodeGenerator
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct(private int $length = 6)
    {
    }

    /**
     * Generate a random base62 string, e.g. "aZ9kLm".
     */
    public function generate(): string
    {
        $alphabetLength = strlen(self::ALPHABET);
        $code = '';

        for ($i = 0; $i < $this->length; $i++) {
            $code .= self::ALPHABET[random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}
