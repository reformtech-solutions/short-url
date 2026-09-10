<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;
use Reformtech\ShortUrl\Laravel\Models\ShortUrlVisit;

class ShortUrlVisited
{
    use Dispatchable;

    public function __construct(
        public readonly ShortUrl $shortUrl,
        public readonly ?ShortUrlVisit $visit,
    ) {
    }
}
