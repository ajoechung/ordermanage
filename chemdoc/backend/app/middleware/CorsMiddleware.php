<?php
namespace app\middleware;

use think\Request;
use think\Response;

class CorsMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->method(true) == 'OPTIONS') {
            return response('', 200, $this->getCorsHeaders());
        }

        $response = $next($request);

        return $this->addCorsHeaders($response);
    }

    protected function getCorsHeaders(): array
    {
        $origin = '*';
        
        return [
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,token',
            'Access-Control-Expose-Headers' => 'Content-Length,Content-Type',
            'Access-Control-Max-Age' => '86400',
        ];
    }

    protected function addCorsHeaders(Response $response): Response
    {
        $origin = '*';
        
        $response->header([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,token',
            'Access-Control-Expose-Headers' => 'Content-Length,Content-Type',
        ]);

        return $response;
    }
}
