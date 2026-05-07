<?php
// +----------------------------------------------------------------------

namespace think;

class Input
{
    protected static $instance;
    protected $data = [];
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public static function instance()
    {
        return self::getInstance();
    }
    
    public function __call($method, $args)
    {
        $type = strtolower(substr($method, 0, 1));
        $name = $args[0] ?? '';
        $default = $args[1] ?? null;
        
        switch ($type) {
            case 's': // string
                return $this->str($name, $default);
            case 'i': // int
                return $this->int($name, $default);
            case 'f': // float
                return $this->float($name, $default);
            case 'b': // bool
                return $this->bool($name, $default);
            default:
                return $this->get($name, $default);
        }
    }
    
    public function str($name = '', $default = '')
    {
        return $this->get($name, $default);
    }
    
    public function int($name = '', $default = 0)
    {
        $value = $this->get($name, $default);
        return intval($value);
    }
    
    public function float($name = '', $default = 0.0)
    {
        $value = $this->get($name, $default);
        return floatval($value);
    }
    
    public function bool($name = '', $default = false)
    {
        $value = $this->get($name, $default);
        return boolval($value);
    }
    
    public function get($name = '', $default = null)
    {
        if ($name === '') {
            return array_merge($_GET, $_POST);
        }
        
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        
        return $default;
    }
    
    public function has($name)
    {
        return isset($_POST[$name]) || isset($_GET[$name]);
    }
    
    public static function getInput()
    {
        return file_get_contents('php://input');
    }
}
