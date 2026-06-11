<?php

declare(strict_types=1);

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),

    'access_token_ttl' => (int) env('JWT_ACCESS_TTL', 3600),

    'refresh_token_ttl' => (int) env('JWT_REFRESH_TTL', 604800),

    'issuer' => env('JWT_ISSUER', env('APP_URL', 'http://localhost')),
];
