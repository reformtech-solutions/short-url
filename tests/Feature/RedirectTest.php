<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Reformtech\ShortUrl\Laravel\Builder;
use Reformtech\ShortUrl\Laravel\Events\ShortUrlVisited;
use Reformtech\ShortUrl\Laravel\Facades\ShortUrl;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl as ShortUrlModel;
use Reformtech\ShortUrl\Tests\TestCase;

class RedirectTest extends TestCase
{
    public function test_visiting_a_short_url_redirects_to_the_destination(): void
    {
        $key = ShortUrl::shorten('https://example.com/destination', 'go-here');

        $response = $this->get("/s/{$key}");

        $response->assertStatus(302);
        $response->assertRedirect('https://example.com/destination');
    }

    public function test_an_unknown_key_returns_404(): void
    {
        $response = $this->get('/s/does-not-exist');

        $response->assertStatus(404);
    }

    public function test_a_single_use_link_returns_404_on_the_second_visit(): void
    {
        $shortUrl = app(Builder::class)
            ->destinationUrl('https://example.com/one-time-offer')
            ->urlKey('one-time')
            ->singleUse()
            ->make();

        $this->get("/s/{$shortUrl->url_key}")->assertStatus(302);
        $this->get("/s/{$shortUrl->url_key}")->assertStatus(404);
    }

    public function test_a_link_before_its_activation_window_returns_404(): void
    {
        $shortUrl = app(Builder::class)
            ->destinationUrl('https://example.com/future-sale')
            ->urlKey('future-sale')
            ->activateAt(now()->addDay())
            ->make();

        $this->get("/s/{$shortUrl->url_key}")->assertStatus(404);
    }

    public function test_a_link_past_its_deactivation_window_returns_404(): void
    {
        $shortUrl = ShortUrlModel::factory()->deactivated()->create([
            'url_key' => 'expired-sale',
        ]);

        $this->get("/s/{$shortUrl->url_key}")->assertStatus(404);
    }

    public function test_visiting_a_tracked_link_records_a_visit_and_fires_an_event(): void
    {
        Event::fake([ShortUrlVisited::class]);

        $shortUrl = app(Builder::class)
            ->destinationUrl('https://example.com/tracked')
            ->urlKey('tracked')
            ->trackVisits()
            ->make();

        $this->get("/s/{$shortUrl->url_key}")->assertStatus(302);

        $this->assertDatabaseHas('short_url_visits', [
            'short_url_id' => $shortUrl->id,
        ]);

        Event::assertDispatched(ShortUrlVisited::class);
    }

    public function test_secure_forces_the_destination_to_https(): void
    {
        $shortUrl = app(Builder::class)
            ->destinationUrl('http://example.com/insecure')
            ->urlKey('secure-link')
            ->secure()
            ->make();

        $this->assertSame('https://example.com/insecure', $shortUrl->destination_url);
    }
}
