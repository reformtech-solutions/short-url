<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $url_key
 * @property string $destination_url
 * @property int $clicks
 * @property bool $single_use
 * @property Carbon|null $used_at
 * @property bool $secure
 * @property int $redirect_status_code
 * @property bool $track_visits
 * @property Carbon|null $activate_at
 * @property Carbon|null $deactivate_at
 * @property-read string $short_url
 */
class ShortUrl extends Model
{
    use HasFactory;

    protected $table = 'short_urls';

    protected $fillable = [
        'url_key',
        'destination_url',
        'clicks',
        'single_use',
        'used_at',
        'secure',
        'redirect_status_code',
        'track_visits',
        'activate_at',
        'deactivate_at',
    ];

    protected $casts = [
        'clicks' => 'integer',
        'single_use' => 'boolean',
        'used_at' => 'datetime',
        'secure' => 'boolean',
        'redirect_status_code' => 'integer',
        'track_visits' => 'boolean',
        'activate_at' => 'datetime',
        'deactivate_at' => 'datetime',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(ShortUrlVisit::class);
    }

    /**
     * Find a ShortUrl model by its key, or null if it doesn't exist.
     */
    public static function findByKey(string $key): ?self
    {
        return static::query()->where('url_key', $key)->first();
    }

    /**
     * Find every ShortUrl model that redirects to the given destination URL.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, static>
     */
    public static function findByDestinationUrl(string $url): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()->where('destination_url', $url)->get();
    }

    /**
     * Whether this link is currently within its activation window
     * (i.e. not yet activated, or already deactivated).
     */
    public function isActive(): bool
    {
        $now = now();

        if ($this->activate_at !== null && $this->activate_at->isFuture()) {
            return false;
        }

        if ($this->deactivate_at !== null && $this->deactivate_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Whether this single-use link has already been consumed.
     */
    public function hasBeenUsed(): bool
    {
        return $this->single_use && $this->used_at !== null;
    }

    public function trackingEnabled(): bool
    {
        return (bool) $this->track_visits;
    }

    /**
     * The list of visit fields enabled for tracking on this link.
     *
     * @return array<int, string>
     */
    public function trackingFields(): array
    {
        $configured = (array) config('short-url.tracking.fields', []);

        return array_keys(array_filter($configured));
    }

    public function getShortUrlAttribute(): string
    {
        $base = rtrim(config('short-url.default_url') ?: config('app.url'), '/');
        $prefix = config('short-url.prefix');

        $path = $prefix ? "/{$prefix}/{$this->url_key}" : "/{$this->url_key}";

        return $base . $path;
    }

    protected static function newFactory()
    {
        return \Reformtech\ShortUrl\Laravel\Models\Factories\ShortUrlFactory::new();
    }
}
