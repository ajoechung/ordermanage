<?php
// +----------------------------------------------------------------------

namespace think;

class Request
{
    protected $param = [];
    protected $get = [];
    protected $post = [];
    protected $server = [];
    protected $header = [];
    protected $input;
    
    protected static $instance;
    
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct()
    {
        $this->server = $_SERVER;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->header = $this->parseHeader();
    }
    
    public function param($name = '', $default = null)
    {
        if (empty($this->param)) {
            $this->param = array_merge($this->get, $this->post);
        }
        
        if ($name === '') {
            return $this->param;
        }
        
        return isset($this->param[$name]) ? $this->param[$name] : $default;
    }
    
    public function get($name = '', $default = null)
    {
        if ($name === '') {
            return $this->get;
        }
        return isset($this->get[$name]) ? $this->get[$name] : $default;
    }
    
    public function post($name = '', $default = null)
    {
        if ($name === '') {
            return $this->post;
        }
        return isset($this->post[$name]) ? $this->post[$name] : $default;
    }
    
    public function isPost()
    {
        return strtolower($this->server['REQUEST_METHOD']) == 'post';
    }
    
    public function isGet()
    {
        return strtolower($this->server['REQUEST_METHOD']) == 'get';
    }
    
    public function isAjax()
    {
        return isset($this->header['x-requested-with']) && 
               strtolower($this->header['x-requested-with']) == 'xmlhttprequest';
    }
    
    public function ip($type = 0, $adv = true)
    {
        static $ip = null;
        
        if ($ip !== null) {
            return $type ? ($ip[$type] ?? $ip[0]) : $ip[0];
        }
        
        if ($adv) {
            if (isset($this->server['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', array_map('trim', $arr));
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($this->server['HTTP_CLIENT_IP'])) {
                $ip = $this->server['HTTP_CLIENT_IP'];
            } elseif (isset($this->server['REMOTE_ADDR'])) {
                $ip = $this->server['REMOTE_ADDR'];
            }
        } elseif (isset($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        }
        
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
        
        return $type ? $ip[$type] : $ip[0];
    }
    
    public function pathinfo()
    {
        if (isset($this->server['PATH_INFO'])) {
            return $this->server['PATH_INFO'];
        }
        
        if (isset($this->server['ORIG_PATH_INFO'])) {
            return str_replace('/index.php', '', $this->server['ORIG_PATH_INFO']);
        }
        
        return '';
    }
    
    public function header($name = '', $default = null)
    {
        if ($name === '') {
            return $this->header;
        }
        
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }
    
    protected function parseHeader()
    {
        $header = [];
        foreach ($this->server as $key => $val) {
            if (strpos($key, 'HTTP_') === 0) {
                $header[substr($key, 5)] = $val;
            }
        }
        return $header;
    }
    
    public function getInput()
    {
        if (is_null($this->input)) {
            $this->input = file_get_contents('php://input');
        }
        return $this->input;
    }
}
