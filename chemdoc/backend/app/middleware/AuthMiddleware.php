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

        $config = config('jwt.');
        try {
            $decoded = JWT::decode($token, new Key($config['secret'], $config['algo']));
            $payload = (array)$decoded;
            
            $request->user_id = $payload['user_id'] ?? 0;
            $request->username = $payload['username'] ?? '';
            $request->user_info = $payload;
            
        } catch (\Exception $e) {
            return json(Result::unauthorized('Token无效或已过期'), 401);
        }

        return $next($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s+(.*?)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        $token = $request->header('token', '');
        if (!empty($token)) {
            return $token;
        }
        
        return $request->param('token', '');
    }
}
