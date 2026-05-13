<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return [
    'secret'   => $_ENV['JWT_SECRET'] ?? 'chemdoc_jwt_secret_key_2026_secure',
    'expire'   => (int)($_ENV['JWT_EXPIRE'] ?? 7200),
    'algo'     => $_ENV['JWT_ALGO'] ?? 'HS256',
    'iss'      => 'chemdoc',
    'aud'      => 'chemdoc_api',
];
