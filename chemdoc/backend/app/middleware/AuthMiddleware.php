<?php
namespace app\middleware;

use app\service\Result;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\Request;
use think\Response;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $this->getTokenFromRequest($request);
        
        if (empty($token)) {
            return json(Result::unauthorized('请先登录'), 401);
        }

        // 直接从.env文件读取配置
        $envPath = dirname(__DIR__, 2) . '/.env';
        $envConfig = [];
        if (is_file($envPath)) {
            $envConfig = parse_ini_file($envPath, true);
        }
        $jwtConfig = $envConfig['JWT'] ?? [];
        
        $secret = $jwtConfig['JWT_SECRET'] ?? 'chemdoc_jwt_secret_key_2026_secure';
        $algo = $jwtConfig['JWT_ALGO'] ?? 'HS256';
        
        try {
            $decoded = JWT::decode($token, new Key($secret, $algo));
            $payload = (array)$decoded;
            
            $request->user_id = $payload['user_id'] ?? 0;
            $request->username = $payload['username'] ?? '';
            $request->user_info = $payload;
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            return json(Result::unauthorized('Token已过期'), 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return json(Result::unauthorized('Token签名无效'), 401);
        } catch (\Exception $e) {
            return json(Result::unauthorized('Token无效'), 401);
        }

        return $next($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        // 优先从Authorization header获取
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s+(.*?)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        // 从token header获取
        $token = $request->header('token', '');
        if (!empty($token)) {
            return $token;
        }
        
        // 从query参数获取
        return $request->param('token', '');
    }
}
