<?php
// +----------------------------------------------------------------------

namespace think;

class Config
{
    protected static $config = [];
    
    public static function get($name = '', $default = null)
    {
        if (empty($name)) {
            return self::$config;
        }
        
        if (strpos($name, '.') === false) {
            return isset(self::$config[$name]) ? self::$config[$name] : $default;
        }
        
        $keys = explode('.', $name);
        $result = self::$config;
        
        foreach ($keys as $key) {
            if (isset($result[$key])) {
                $result = $result[$key];
            } else {
                return $default;
            }
        }
        
        return $result;
    }
    
    public static function set($name, $value = null)
    {
        if (is_array($name)) {
            self::$config = array_merge(self::$config, $name);
        } elseif (strpos($name, '.') === false) {
            self::$config[$name] = $value;
        } else {
            $keys = explode('.', $name);
            $config = &self::$config;
            
            foreach ($keys as $key) {
                if (!isset($config[$key])) {
                    $config[$key] = [];
                }
                $config = &$config[$key];
            }
            
            $config = $value;
        }
    }
    
    public static function has($name)
    {
        if (strpos($name, '.') === false) {
            return isset(self::$config[$name]);
        }
        
        $keys = explode('.', $name);
        $result = self::$config;
        
        foreach ($keys as $key) {
            if (isset($result[$key])) {
                $result = $result[$key];
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    public static function load($file)
    {
        if (file_exists($file)) {
            $config = include $file;
            if (is_array($config)) {
                self::$config = array_merge(self::$config, $config);
            }
        }
    }
}
