-- Plain PHP (non-Laravel) users: run this manually to set up the table
-- for use with YourVendor\UrlShortener\Storage\PdoStorage.
--
-- Laravel users don't need this — the migration in database/migrations/
-- is auto-published/run instead.

CREATE TABLE IF NOT EXISTS short_urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    original_url TEXT NOT NULL,
    clicks INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    INDEX idx_code (code)
);
