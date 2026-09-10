<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Reformtech\ShortUrl\CodeGenerator;
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;

/**
 * @extends Factory<ShortUrl>
 */
class ShortUrlFactory extends Factory
{
    protected $model = ShortUrl::class;

    public function definition(): array
    {
        return [
            'url_key' => (new CodeGenerator(6))->generate(),
            'destination_url' => $this->faker->url(),
            'clicks' => 0,
            'single_use' => false,
            'used_at' => null,
            'secure' => false,
            'redirect_status_code' => 302,
            'track_visits' => true,
            'activate_at' => null,
            'deactivate_at' => null,
        ];
    }

    /**
     * The link has an activation window that has already ended.
     */
    public function deactivated(): self
    {
        return $this->state(fn () => [
            'deactivate_at' => now()->subDay(),
        ]);
    }

    /**
     * The link has an activation window that hasn't started yet.
     */
    public function inactive(): self
    {
        return $this->state(fn () => [
            'activate_at' => now()->addDay(),
        ]);
    }
}
