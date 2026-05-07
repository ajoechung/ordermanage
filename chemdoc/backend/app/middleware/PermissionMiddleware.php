<?php
namespace app\middleware;

use app\service\Auth;
use app\service\Result;
use think\Request;
use think\Response;

class PermissionMiddleware
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $userId = $request->user_id ?? 0;
        $ruleName = $this->getRuleName($request);
        
        if ($userId == 1) {
            return $next($request);
        }
        
        if (!$this->auth->check($ruleName, $userId)) {
            return json(Result::forbidden('无权限访问该接口'), 403);
        }

        return $next($request);
    }

    protected function getRuleName(Request $request): string
    {
        $prefix = $request->module() ?: 'api';
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        
        return $prefix . '/' . $controller . '/' . $action;
    }
}
