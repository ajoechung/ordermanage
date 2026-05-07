<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think;

class Container
{
    protected static $instance;
    protected $bind = [];
    protected $instances = [];
    
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    
    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }
    
    public function bind($abstract, $concrete = null)
    {
        if ($concrete instanceof \Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            $this->instances[$abstract] = $concrete;
        } else {
            $this->bind[$abstract] = $concrete;
        }
    }
    
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }
    
    public function get($name, $args = [])
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        
        if (isset($this->bind[$name])) {
            $concrete = $this->bind[$name];
            if ($concrete instanceof \Closure) {
                return $concrete($this);
            }
            return $this->get($concrete, $args);
        }
        
        if (class_exists($name)) {
            return $this->make($name, $args);
        }
        
        return null;
    }
    
    public function make($abstract, $args = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        if (isset($this->bind[$abstract])) {
            $concrete = $this->bind[$abstract];
            if ($concrete instanceof \Closure) {
                $object = $concrete($this, $args);
            } else {
                $object = $this->make($concrete, $args);
            }
        } else {
            $object = $this->invokeClass($abstract, $args);
        }
        
        $this->instances[$abstract] = $object;
        
        return $object;
    }
    
    protected function invokeClass($class, $args = [])
    {
        $reflect = new \ReflectionClass($class);
        
        if (!$reflect->isInstantiable()) {
            throw new \Exception('Class not instantiable: ' . $class);
        }
        
        $constructor = $reflect->getConstructor();
        
        if ($constructor) {
            $params = $constructor->getParameters();
            $args = $this->bindParams($params, $args);
            return $reflect->newInstanceArgs($args);
        }
        
        return new $class;
    }
    
    protected function bindParams($params, $args)
    {
        $result = [];
        foreach ($params as $param) {
            $name = $param->getName();
            $class = $param->getClass();
            
            if ($class) {
                $result[] = $this->make($class->getName());
            } elseif (isset($args[$name])) {
                $result[] = $args[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $result[] = $param->getDefaultValue();
            }
        }
        return $result;
    }
    
    public function invoke($class, $method, $vars = [])
    {
        if (is_string($class)) {
            $class = $this->make($class);
        }
        
        $reflect = new \ReflectionMethod($class, $method);
        $params = $reflect->getParameters();
        $args = $this->bindParams($params, $vars);
        
        return $reflect->invokeArgs($class, $args);
    }
    
    public function invokeFunction($function, $vars = [])
    {
        $reflect = new \ReflectionFunction($function);
        $params = $reflect->getParameters();
        $args = $this->bindParams($params, $vars);
        
        return $reflect->invokeArgs($args);
    }
}
