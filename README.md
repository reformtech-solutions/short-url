# Short URL

[![Latest Version on Packagist](https://img.shields.io/packagist/v/reformtech/short-url.svg?style=flat-square)](https://packagist.org/packages/reformtech/short-url)
[![Total Downloads](https://img.shields.io/packagist/dt/reformtech/short-url.svg?style=flat-square)](https://packagist.org/packages/reformtech/short-url)
[![Tests](https://img.shields.io/github/actions/workflow/status/reformtech-solutions/short-url/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/reformtech-solutions/short-url/actions)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/reformtech/short-url.svg?style=flat-square)](https://packagist.org/packages/reformtech/short-url)
[![License](https://img.shields.io/github/license/reformtech-solutions/short-url.svg?style=flat-square)](LICENSE.md)

A URL shortener for PHP — usable standalone in **any PHP project**, or with a
first-class integration layer for **Laravel**: single-use links, activation
windows, HTTPS enforcement, visit tracking, and events.

Unlike most short-URL packages, the core has **zero framework dependencies**
and **no required PHP extensions** beyond what ships with PHP itself — the
Laravel layer is an optional add-on, not a requirement.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
  - [Requirements](#requirements)
  - [Plain PHP Setup](#plain-php-setup)
  - [Laravel Setup](#laravel-setup)
- [Usage: Plain PHP](#usage-plain-php)
  - [Quick Start](#quick-start)
  - [Custom Storage Backends](#custom-storage-backends)
- [Usage: Laravel](#usage-laravel)
  - [Quick Start](#quick-start-1)
  - [The Fluent Builder](#the-fluent-builder)
  - [Custom Keys](#custom-keys)
  - [Single Use Links](#single-use-links)
  - [Enforcing HTTPS](#enforcing-https)
  - [Redirect Status Code](#redirect-status-code)
  - [Activation and Deactivation Windows](#activation-and-deactivation-windows)
  - [Custom Fields](#custom-fields)
  - [Conditionals](#conditionals)
  - [Tracking Visitors](#tracking-visitors)
  - [Events](#events)
  - [Helper Methods](#helper-methods)
  - [Model Factories](#model-factories)
- [Configuration Reference](#configuration-reference)
- [Testing](#testing)
- [Static Analysis](#static-analysis)
- [Security](#security)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

## Overview

This package solves one problem two ways:

| | Plain PHP | Laravel |
|---|---|---|
| Dependencies | None | `illuminate/support`, `illuminate/database` |
| Storage | `ArrayStorage`, `PdoStorage`, or your own | Eloquent, auto-migrated |
| API | `UrlShortener::shorten()` / `resolve()` | Fluent `Builder`, or the same simple API via a facade |
| Redirect handling | You wire it up | Auto-registered route + controller |
| Advanced features (single-use, tracking, windows) | — | ✅ |

If you're in Symfony, WordPress, a legacy codebase, or a CLI tool, use the
core directly. If you're in Laravel, install the same package and get a
zero-config, feature-rich experience out of the box.

## Installation

### Requirements

- PHP 8.1+
- For the Laravel layer: Laravel 10.0+ (`illuminate/support`, `illuminate/database`)

```bash
composer require reformtech/short-url
```

### Plain PHP Setup

Run the provided schema once against your database:

```bash
mysql -u root -p your_database < vendor/reformtech/short-url/database/schema.sql
```

(Or adapt it for Postgres/SQLite — it's a single, simple `CREATE TABLE` statement.)

### Laravel Setup

The service provider and facade are auto-discovered — no manual registration needed.

Publish the config and run the migrations:

```bash
php artisan vendor:publish --tag=short-url-config
php artisan migrate
```

This creates two tables: `short_urls` and `short_url_visits`.

## Usage: Plain PHP

### Quick Start

```php
use Reformtech\ShortUrl\UrlShortener;
use Reformtech\ShortUrl\Storage\PdoStorage;

$pdo = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$shortener = new UrlShortener(new PdoStorage($pdo));

$key = $shortener->shorten('https://example.com/some/very/long/url');
// "aZ9kL"

$original = $shortener->resolve($key);
// "https://example.com/some/very/long/url"

$shortener->forget($key);
```

Custom keys work the same way as in Laravel:

```php
$shortener->shorten('https://example.com/sale', 'summer-sale');
```

For quick scripts or tests, skip the database entirely:

```php
use Reformtech\ShortUrl\Storage\ArrayStorage;

$shortener = new UrlShortener(new ArrayStorage());
```

### Custom Storage Backends

Implement `Reformtech\ShortUrl\Contracts\StorageInterface` (five methods)
to back the shortener with Redis, MongoDB, or any storage shape you like:

```php
use Reformtech\ShortUrl\Contracts\StorageInterface;

class RedisStorage implements StorageInterface
{
    public function save(string $code, string $originalUrl, ?\DateTimeInterface $expiresAt = null): void { /* ... */ }
    public function find(string $code): ?string { /* ... */ }
    public function exists(string $code): bool { /* ... */ }
    public function delete(string $code): bool { /* ... */ }
    public function incrementClicks(string $code): void { /* ... */ }
}
```

## Usage: Laravel

### Quick Start

```php
use Reformtech\ShortUrl\Laravel\Facades\ShortUrl;

$key = ShortUrl::shorten('https://example.com/some/long/url');
// "aZ9kL"

$original = ShortUrl::resolve($key);

ShortUrl::forget($key);
```

Visiting the generated short URL (default: `/s/{key}`) automatically
redirects to the destination — the route is registered for you.

### The Fluent Builder

For anything beyond the basics — single-use links, tracking, scheduling —
use the fluent builder via the same facade:

```php
use Reformtech\ShortUrl\Laravel\Facades\ShortUrl;

$shortUrl = ShortUrl::destinationUrl('https://example.com/launch')
    ->urlKey('product-launch')
    ->singleUse()
    ->trackVisits()
    ->make();

$shortUrl->short_url; // "https://yourapp.com/s/product-launch"
```

`make()` returns the underlying `ShortUrl` Eloquent model, so you get full
access to its attributes and relationships.

### Custom Keys

```php
ShortUrl::destinationUrl('https://example.com/sale')
    ->urlKey('summer-sale')
    ->make();

// https://yourapp.com/s/summer-sale
```

Omit `->urlKey()` and a random key is generated, using the length set in
`key_length` in the config (default: 5).

### Single Use Links

Once visited, a single-use link returns a `404` on every subsequent visit:

```php
ShortUrl::destinationUrl('https://example.com/one-time-offer')
    ->singleUse()
    ->make();
```

### Enforcing HTTPS

Forces the destination URL to `https://`, regardless of what was passed in:

```php
ShortUrl::destinationUrl('http://example.com')
    ->secure()
    ->make();

// Destination stored as: https://example.com
```

### Redirect Status Code

Defaults to `302` (recommended, so tracking/events reliably fire on every
visit — browsers tend to cache `301`s and skip re-visiting the short URL).

```php
ShortUrl::destinationUrl('https://example.com')
    ->redirectStatusCode(301)
    ->make();
```

### Activation and Deactivation Windows

Useful for scheduled marketing campaigns — a link that only works during a
set window:

```php
ShortUrl::destinationUrl('https://example.com/black-friday')
    ->activateAt(now()->addDays(3))
    ->deactivateAt(now()->addDays(4))
    ->make();
```

Outside the window, visitors get a `404`.

### Custom Fields

Attach your own columns (e.g. to associate a link with a tenant or user) via
`beforeCreate()` — just remember to add the column via your own migration
first:

```php
use Reformtech\ShortUrl\Laravel\Models\ShortUrl as ShortUrlModel;

ShortUrl::destinationUrl('https://example.com')
    ->beforeCreate(function (ShortUrlModel $model) {
        $model->tenant_id = auth()->user()->tenant_id;
    })
    ->make();
```

### Conditionals

The builder supports Laravel's `when()`/`unless()` via the `Conditionable` trait:

```php
ShortUrl::destinationUrl($url)
    ->when($request->boolean('single_use'), fn ($builder) => $builder->singleUse())
    ->make();
```

### Tracking Visitors

Tracking is on by default (configurable). Each visit records IP address,
user agent, a best-effort device type (`desktop`/`mobile`/`tablet`/`robot`),
and the referer URL — each field individually toggleable in config, or
per-link:

```php
ShortUrl::destinationUrl('https://example.com')
    ->trackVisits()
    ->make();
```

Access recorded visits via the relationship:

```php
$shortUrl = ShortUrlModel::findByKey('summer-sale');
$visits = $shortUrl->visits; // Collection of ShortUrlVisit
```

### Events

Every visit dispatches `Reformtech\ShortUrl\Laravel\Events\ShortUrlVisited`,
carrying the `ShortUrl` model and the recorded `ShortUrlVisit` (or `null` if
tracking was disabled for that link):

```php
use Reformtech\ShortUrl\Laravel\Events\ShortUrlVisited;

class SendSlackNotification
{
    public function handle(ShortUrlVisited $event): void
    {
        // $event->shortUrl, $event->visit
    }
}
```

> **Note:** with a `301` redirect, browsers often cache the destination and
> skip re-visiting the short URL on subsequent clicks — so the event may not
> fire every time. Use `302` (the default) if you need reliable per-visit events.

### Helper Methods

```php
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;

// Find by key
$shortUrl = ShortUrl::findByKey('summer-sale');

// Find every link that points to a given destination
$shortUrls = ShortUrl::findByDestinationUrl('https://example.com/sale');

// Check state
$shortUrl->isActive();
$shortUrl->hasBeenUsed();
$shortUrl->trackingEnabled();
$shortUrl->trackingFields(); // ['ip_address', 'user_agent', ...]
```

### Model Factories

```php
use Reformtech\ShortUrl\Laravel\Models\ShortUrl;

$shortUrl = ShortUrl::factory()->create();
$deactivated = ShortUrl::factory()->deactivated()->create();
$notYetActive = ShortUrl::factory()->inactive()->create();
```

## Configuration Reference

After publishing, `config/short-url.php` contains:

| Key | Default | Description |
|---|---|---|
| `default_url` | `null` | Base domain for short URLs. `null` falls back to `app.url`. |
| `prefix` | `s` | Route prefix (`/{prefix}/{key}`). Set `null` to remove it. |
| `disable_default_route` | `false` | Set `true` if you're registering your own route to `RedirectController`. |
| `middleware` | `[]` | Middleware applied to the package's default route. |
| `key_length` | `5` | Minimum length of auto-generated keys. |
| `redirect_status_code` | `302` | Default HTTP status for redirects (overridable per link). |
| `allowed_url_schemes` | `['http://', 'https://']` | Schemes accepted when shortening a URL. |
| `tracking.default_enabled` | `true` | Whether new links track visits by default. |
| `tracking.fields.*` | all `true` | Individually toggle which visit fields are recorded. |

## Testing

```bash
composer install
vendor/bin/phpunit
```

## Static Analysis

```bash
vendor/bin/phpstan analyse
```

## Security

If you discover a security vulnerability, please email
`info@reformtech.in` instead of opening a public issue.

## Contributing

Pull requests are welcome. For significant changes, please open an issue
first to discuss what you'd like to change.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of changes.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
