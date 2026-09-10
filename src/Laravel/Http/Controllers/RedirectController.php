<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Reformtech\ShortUrl\Laravel\Events\ShortUrlVisited;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;
use Reformtech\ShortUrl\Laravel\Models\ShortUrlVisit;

class RedirectController extends Controller
{
    public function __invoke(string $shortUrlKey, Request $request): RedirectResponse
    {
        $shortUrl = ShortUrl::findByKey($shortUrlKey);

        if ($shortUrl === null || ! $shortUrl->isActive() || $shortUrl->hasBeenUsed()) {
            abort(404, 'Short URL not found.');
        }

        ShortUrl::query()->where('id', $shortUrl->id)->increment('clicks');

        if ($shortUrl->single_use) {
            $shortUrl->forceFill(['used_at' => now()])->save();
        }

        $visit = null;

        if ($shortUrl->trackingEnabled()) {
            $visit = $this->recordVisit($shortUrl, $request);
        }

        ShortUrlVisited::dispatch($shortUrl, $visit);

        return redirect()->away(
            $shortUrl->destination_url,
            $shortUrl->redirect_status_code,
        );
    }

    private function recordVisit(ShortUrl $shortUrl, Request $request): ShortUrlVisit
    {
        $fields = (array) config('short-url.tracking.fields', []);

        /** @var ShortUrlVisit $visit */
        $visit = $shortUrl->visits()->create([
            'ip_address' => ($fields['ip_address'] ?? true) ? $request->ip() : null,
            'user_agent' => ($fields['user_agent'] ?? true) ? $request->userAgent() : null,
            'device_type' => ($fields['device_type'] ?? true) ? $this->guessDeviceType($request->userAgent()) : null,
            'referer_url' => ($fields['referer_url'] ?? true) ? $request->headers->get('referer') : null,
            'visited_at' => now(),
        ]);

        return $visit;
    }

    /**
     * Lightweight, dependency-free device sniffing from the user agent string.
     * Good enough for basic analytics; swap in a dedicated parser package
     * in your own app if you need more precision.
     */
    private function guessDeviceType(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'unknown';
        }

        if (preg_match('/bot|crawler|spider|crawling/i', $userAgent)) {
            return 'robot';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }
}
