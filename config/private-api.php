<?php

return [
    'enabled' => env('PRIVATE_API_ENABLED', false),

    'resources' => [
        'collections' => false,
        'navs' => false,
        'taxonomies' => false,
        'assets' => false,
        'globals' => false,
        'forms' => false,
        'users' => false,
    ],

    'route' => env('PRIVATE_API_ROUTE', 'api/private'),

    'middleware' => env('PRIVATE_API_MIDDLEWARE', 'auth:api'),
];
