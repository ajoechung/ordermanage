<?php
// +----------------------------------------------------------------------

namespace think;

class Session
{
    protected static $prefix = 'think';
    
    public static function init()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function get($name = '', $default = null)
    {
        self::init();
        
        if ($name === '') {
            return $_SESSION;
        }
        
        $name = self::$prefix . $name;
        
        if (strpos($name, '.') !== false) {
            $keys = explode('.', $name);
            $result = $_SESSION;
            foreach ($keys as $key) {
                if (isset($result[$key])) {
                    $result = $result[$key];
                } else {
                    return $default;
                }
            }
            return $result;
        }
        
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }
    
    public static function set($name, $value = '')
    {
        self::init();
        
        if (is_array($name)) {
            foreach ($name as $key => $val) {
                $_SESSION[self::$prefix . $key] = $val;
            }
        } else {
            $_SESSION[self::$prefix . $name] = $value;
        }
        
        return true;
    }
    
    public static function delete($name = '')
    {
        self::init();
        
        if ($name === '') {
            $_SESSION = [];
        } else {
            unset($_SESSION[self::$prefix . $name]);
        }
        
        return true;
    }
    
    public static function has($name)
    {
        self::init();
        return isset($_SESSION[self::$prefix . $name]);
    }
    
    public static function flash($name, $value = null)
    {
        if (is_null($value)) {
            $flash = self::get('_flash_' . $name);
            self::delete('_flash_' . $name);
            return $flash;
        }
        return self::set('_flash_' . $name, $value);
    }
}
