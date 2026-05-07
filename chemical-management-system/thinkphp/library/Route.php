<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think;

class Route
{
    protected static $rules = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
        '*'      => [],
    ];
    
    public static function get($rule, $route = '')
    {
        return self::addRule('GET', $rule, $route);
    }
    
    public static function post($rule, $route = '')
    {
        return self::addRule('POST', $rule, $route);
    }
    
    public static function put($rule, $route = '')
    {
        return self::addRule('PUT', $rule, $route);
    }
    
    public static function delete($rule, $route = '')
    {
        return self::addRule('DELETE', $rule, $route);
    }
    
    public static function any($rule, $route = '')
    {
        return self::addRule('*', $rule, $route);
    }
    
    protected static function addRule($method, $rule, $route)
    {
        if (is_array($rule)) {
            foreach ($rule as $key => $val) {
                self::$rules[$method][$key] = $val;
            }
        } else {
            self::$rules[$method][$rule] = $route;
        }
        return new self();
    }
    
    public static function group($name, $routes = [])
    {
        if (is_callable($routes)) {
            $routes();
        }
    }
    
    public static function match($methods, $rule, $route = '')
    {
        foreach ((array)$methods as $method) {
            self::$rules[$method][$rule] = $route;
        }
        return new self();
    }
}
