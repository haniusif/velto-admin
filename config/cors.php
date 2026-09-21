<?php

/*
 * Only the Flutter web app (web.velto.sa) calls the API from another origin;
 * the native apps have no origin and the website is same-origin. Everything
 * else is refused at the browser. Local dev origins are added when APP_ENV
 * is local so `flutter run -d chrome` still works.
 */
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'https://web.velto.sa'))))),
    'allowed_origins_patterns' => env('APP_ENV') === 'local' ? ['#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#'] : [],
    'allowed_headers' => ['Accept', 'Accept-Language', 'Authorization', 'Content-Type', 'X-Requested-With'],
    'exposed_headers' => ['Retry-After'],
    'max_age' => 3600,
    'supports_credentials' => false,
];
