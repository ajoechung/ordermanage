<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

class App
{
    protected $instance = [];
    protected $namespace = 'app';
    protected $beginTime;
    protected $beginMem;
    
    public function run()
    {
        $this->beginTime = microtime(true);
        $this->beginMem = memory_get_usage();
        
        // 解析URL
        $request = Request::instance();
        
        // 应用初始化
        $this->init();
        
        // 获取路由
        $dispatch = $this->route($request);
        
        // 执行调度
        $data = $this->dispatch($request, $dispatch);
        
        // 输出数据
        if ($data instanceof Response) {
            $response = $data;
        } elseif (!is_null($data)) {
            $response = Response::create($data);
        } else {
            $response = Response::create();
        }
        
        return $response;
    }
    
    protected function init()
    {
        // 加载配置文件
        $this->loadConfig();
        
        // 注册错误处理
        $this->registerError();
        
        // 初始化容器
        Container::setInstance($this);
    }
    
    protected function loadConfig()
    {
        $config = [
            'default_timezone' => 'Asia/Shanghai',
            'app_debug' => true,
            'default_return_type' => 'html',
        ];
        
        foreach ($config as $key => $val) {
            if (!defined($key)) {
                define($key, $val);
            }
        }
        
        Config::set($config);
        
        // 加载应用配置
        $appConfig = include APP_PATH . 'config.php';
        if (is_array($appConfig)) {
            Config::set($appConfig);
        }
        
        // 加载数据库配置
        $dbConfig = include APP_PATH . 'database.php';
        if (is_array($dbConfig)) {
            Config::set('database', $dbConfig);
        }
    }
    
    protected function registerError()
    {
        error_reporting(E_ALL);
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']);
        register_shutdown_function([$this, 'appShutdown']);
    }
    
    public function appError($errno, $errstr, $errfile = '', $errline = 0)
    {
        $exception = new \Exception($errstr, $errno);
        $this->appException($exception);
    }
    
    public function appException(\Throwable $e)
    {
        Log::record($e->getMessage(), 'error');
        if (APP_DEBUG) {
            echo '<pre>' . $e . '</pre>';
        }
    }
    
    public function appShutdown()
    {
        $error = error_get_last();
        if ($error && !in_array($error['type'], [E_NOTICE, E_WARNING])) {
            $this->appException(new \Exception($error['message'], $error['type']));
        }
    }
    
    protected function route(Request $request)
    {
        // 解析路由
        $pathinfo = $request->pathinfo();
        $pathinfo = trim($pathinfo, '/');
        
        if (empty($pathinfo)) {
            return $this->routeDefault($request);
        }
        
        // URL路由
        $routes = include APP_PATH . 'route.php';
        
        if (isset($routes[$pathinfo])) {
            return $this->parseUrl($routes[$pathinfo]);
        }
        
        // 自动解析控制器/方法
        $path = explode('/', $pathinfo);
        $controller = isset($path[0]) ? $path[0] : 'index';
        $action = isset($path[1]) ? $path[1] : 'index';
        
        return [
            'type' => 'module',
            'controller' => $controller,
            'action' => $action,
        ];
    }
    
    protected function routeDefault(Request $request)
    {
        return [
            'type' => 'module',
            'controller' => 'index',
            'action' => 'index',
        ];
    }
    
    protected function parseUrl($route)
    {
        if (is_string($route)) {
            $route = explode('/', $route);
            return [
                'type' => 'module',
                'controller' => isset($route[0]) ? $route[0] : 'index',
                'action' => isset($route[1]) ? $route[1] : 'index',
            ];
        }
        return $route;
    }
    
    protected function dispatch(Request $request, $dispatch)
    {
        switch ($dispatch['type']) {
            case 'module':
                return $this->execModule($request, $dispatch);
            default:
                return $this->execModule($request, $dispatch);
        }
    }
    
    protected function execModule(Request $request, $dispatch)
    {
        $controller = isset($dispatch['controller']) ? $dispatch['controller'] : 'index';
        $action = isset($dispatch['action']) ? $dispatch['action'] : 'index';
        
        // 解析控制器
        $class = $this->parseController($controller);
        
        if (!class_exists($class)) {
            throw new \Exception('Controller not exists: ' . $class);
        }
        
        $controller = new $class();
        
        if (!method_exists($controller, $action)) {
            throw new \Exception('Action not exists: ' . $class . '->' . $action);
        }
        
        // 执行方法
        $data = $this->invokeMethod($controller, $action);
        
        return $data;
    }
    
    protected function parseController($controller)
    {
        $controller = str_replace('.', '\\', $controller);
        
        if (strpos($controller, '\\') === 0) {
            return $controller;
        }
        
        $namespace = $this->namespace;
        
        if (strpos($controller, '/') !== false) {
            $pos = strpos($controller, '/');
            $module = substr($controller, 0, $pos);
            $controller = substr($controller, $pos + 1);
            $namespace = $namespace . '\\' . ucfirst($module);
        }
        
        return $namespace . '\\controller\\' . ucfirst($controller);
    }
    
    protected function invokeMethod($controller, $action)
    {
        $method = $action . 'Action';
        
        // 前置方法
        if (method_exists($controller, '_initialize')) {
            $controller->_initialize();
        }
        
        // 调用方法
        $data = $controller->$method();
        
        // 后置方法
        if (method_exists($controller, '_empty')) {
            $controller->_empty();
        }
        
        return $data;
    }
    
    public function __get($name)
    {
        return $this->get($name);
    }
    
    public function get($name)
    {
        return Container::getInstance()->get($name);
    }
}
