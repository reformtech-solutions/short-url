<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Base URL
    |--------------------------------------------------------------------------
    | The base URL that your short URLs will be built with. Leave this as
    | null to use your application's own "app.url" config value — useful
    | if you want to serve short links from a separate/branded domain.
    */
    'default_url' => env('SHORT_URL_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | Short URLs are served at /{prefix}/{key}, e.g. /s/aZ9kL. Set this to
    | null to remove the prefix entirely (e.g. /{key}).
    */
    'prefix' => 's',

    /*
    |--------------------------------------------------------------------------
    | Disable the Default Route
    |--------------------------------------------------------------------------
    | If you're registering your own custom route to the package's
    | RedirectController, set this to true to stop the package
    | registering its own default route.
    */
    'disable_default_route' => false,

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to the package's own default route. Ignored if
    | you're using a custom route (apply middleware there instead).
    */
    'middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Key Length
    |--------------------------------------------------------------------------
    | Minimum length of auto-generated short URL keys. A minimum of 3 is
    | enforced. If every key of this length is already taken, the
    | generator will fall back to a longer one automatically.
    */
    'key_length' => 5,

    /*
    |--------------------------------------------------------------------------
    | Redirect Status Code
    |--------------------------------------------------------------------------
    | Default HTTP status used when redirecting a visitor. 302 (temporary)
    | is recommended: most browsers won't cache it, so tracking/click
    | events keep firing on every visit. Use 301 for a permanent redirect.
    | This can be overridden per-link via the Builder.
    */
    'redirect_status_code' => 302,

    /*
    |--------------------------------------------------------------------------
    | Allowed URL Schemes
    |--------------------------------------------------------------------------
    | Only URLs with one of these schemes can be shortened.
    */
    'allowed_url_schemes' => [
        'http://',
        'https://',
    ],

    /*
    |--------------------------------------------------------------------------
    | Visit Tracking
    |--------------------------------------------------------------------------
    | Controls whether visits are recorded by default, and which fields
    | are captured. Each option can be overridden per-link via the
    | Builder (e.g. ->trackVisits(false)).
    */
    'tracking' => [
        'default_enabled' => true,

        'fields' => [
            'ip_address' => true,
            'user_agent' => true,
            'device_type' => true,
            'referer_url' => true,
        ],
    ],

];
