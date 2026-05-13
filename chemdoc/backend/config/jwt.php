<?php
$envPath = dirname(__DIR__) . '/.env';
$envConfig = [];

if (is_file($envPath)) {
    $envConfig = parse_ini_file($envPath, true);
}

$jwtConfig = $envConfig['JWT'] ?? [];

return [
    'secret'   => $jwtConfig['JWT_SECRET'] ?? 'chemdoc_jwt_secret_key_2026_secure',
    'expire'   => (int)($jwtConfig['JWT_EXPIRE'] ?? 7200),
    'algo'     => $jwtConfig['JWT_ALGO'] ?? 'HS256',
    'iss'      => 'chemdoc',
    'aud'      => 'chemdoc_api',
];
