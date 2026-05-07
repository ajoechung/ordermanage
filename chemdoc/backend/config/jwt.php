<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return [
    'secret'   => env('jwt.secret', 'chemdoc_jwt_secret_key_2026_secure'),
    'expire'   => env('jwt.expire', 7200),
    'algo'     => env('jwt.algo', 'HS256'),
    'iss'      => 'chemdoc',
    'aud'      => 'chemdoc_api',
];

function generateToken(array $payload): string
{
    $config = config('jwt');
    $payload['iss'] = $config['iss'];
    $payload['aud'] = $config['aud'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $config['expire'];
    
    return JWT::encode($payload, $config['secret'], $config['algo']);
}

function verifyToken(string $token): array
{
    $config = config('jwt');
    try {
        $decoded = JWT::decode($token, new Key($config['secret'], $config['algo']));
        return ['code' => 1, 'data' => (array)$decoded];
    } catch (\Exception $e) {
        return ['code' => 0, 'msg' => $e->getMessage()];
    }
}

function getTokenFromRequest(): ?string
{
    $header = request()->header('Authorization', '');
    if (preg_match('/Bearer\s+(.*?)$/i', $header, $matches)) {
        return $matches[1];
    }
    return null;
}
