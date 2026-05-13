<?php
return [
    'app' => [
        'namespace' => 'app',
        'exception_handle' => 'app\exception\ExceptionHandle',
    ],
    'middleware' => [
        \app\middleware\CorsMiddleware::class,
    ],
    'route' => [
        'url_route_on' => true,
        'url_route_must' => false,
        'route_config_file' => ['app'],
    ],
    'request' => [
        'default_return_type' => 'json',
        'default_ajax_return' => 'json',
    ],
    'response' => [
        'default_return_type' => 'json',
    ],
    'database' => require __DIR__ . '/../config/database.php',
    'cache' => require __DIR__ . '/../config/cache.php',
    'jwt' => require __DIR__ . '/../config/jwt.php',
    'upload' => require __DIR__ . '/../config/upload.php',
];
