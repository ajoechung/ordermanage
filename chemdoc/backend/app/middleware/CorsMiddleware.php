<?php
namespace app\middleware;

use think\Request;
use think\Response;

class CorsMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->method(true) == 'OPTIONS') {
            return response('', 200)->header($this->getCorsHeaders());
        }

        $response = $next($request);

        return $this->addCorsHeaders($response);
    }

    protected function getCorsHeaders(): array
    {
        $origin = $this->getOrigin();
        
        return [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, token, Accept, Origin',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Type, Authorization',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Credentials' => 'true',
        ];
    }

    protected function addCorsHeaders(Response $response): Response
    {
        $origin = $this->getOrigin();
        
        $response->header([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, token, Accept, Origin',
            'Access-Control-Expose-Headers' => 'Content-Length, Content-Type, Authorization',
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        return $response;
    }

    protected function getOrigin(): string
    {
        $header = request()->header('Origin', '');
        $allowedOrigins = [
            'http://blog.ajoe.cn',
            'http://szy.ajoe.cn',
            'https://blog.ajoe.cn',
            'https://szy.ajoe.cn',
        ];
        
        if (in_array($header, $allowedOrigins)) {
            return $header;
        }
        
        return '*';
    }
}
