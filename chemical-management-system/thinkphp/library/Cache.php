<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think;

class Cache
{
    protected static $instance;
    protected $handler;
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $config = Config::get('cache') ?: [];
            $type = isset($config['type']) ? $config['type'] : 'File';
            $class = '\\think\\cache\\' . ucfirst($type);
            
            self::$instance = new $class($config);
        }
        
        return self::$instance;
    }
    
    public static function get($name, $default = null)
    {
        return self::getInstance()->get($name, $default);
    }
    
    public static function set($name, $value, $expire = null)
    {
        return self::getInstance()->set($name, $value, $expire);
    }
    
    public static function delete($name)
    {
        return self::getInstance()->delete($name);
    }
    
    public static function clear()
    {
        return self::getInstance()->clear();
    }
    
    public static function has($name)
    {
        return self::getInstance()->has($name);
    }
    
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::getInstance(), $method], $args);
    }
}
