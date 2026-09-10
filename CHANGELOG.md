# Changelog

All notable changes to `reformtech/short-url` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2026-09-10

### Added
- `Reformtech\ShortUrl\Http\RedirectHandler` — handles the full "read short
  code from the request, resolve it, send the redirect" flow for plain PHP
  projects, so it no longer needs to be hand-written per project. Mirrors
  what the Laravel `RedirectController` already did automatically.

## [0.1.0] - 2026-09-10

### Added
- Framework-agnostic `UrlShortener` core with `StorageInterface` contract.
- `ArrayStorage` (in-memory) and `PdoStorage` (any PDO-backed database) adapters for plain PHP usage.
- Laravel service provider, auto-registered redirect route, and publishable config.
- Fluent `Builder` API (`ShortUrl::destinationUrl(...)->urlKey(...)->make()`) for Laravel.
- Single-use links.
- Activation and deactivation windows for scheduled links.
- HTTPS enforcement via `->secure()`.
- Configurable per-link redirect status codes.
- Visit tracking (IP address, user agent, device type, referer URL) with an opt-out per link and per field.
- `ShortUrlVisited` event, dispatched on every visit.
- `ShortUrl::findByKey()` and `ShortUrl::findByDestinationUrl()` helper methods.
- Model factories, including `deactivated()` and `inactive()` states.
- PHPUnit + Orchestra Testbench test suite.
- GitHub Actions CI (test matrix across PHP 8.1–8.3 and Laravel 10–11) and PHPStan static analysis.
