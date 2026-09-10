<?php

declare(strict_types=1);

namespace Reformtech\ShortUrl\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $short_url_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_type
 * @property string|null $referer_url
 * @property Carbon $visited_at
 */
class ShortUrlVisit extends Model
{
    public $timestamps = false;

    protected $table = 'short_url_visits';

    protected $fillable = [
        'short_url_id',
        'ip_address',
        'user_agent',
        'device_type',
        'referer_url',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrl::class);
    }
}
