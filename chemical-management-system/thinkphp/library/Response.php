<?php
// +----------------------------------------------------------------------

namespace think;

class Response
{
    protected $data;
    protected $type = 'html';
    protected $code = 200;
    protected $header = [];
    
    public static function create($data = '', $type = '', $code = 200, $header = [])
    {
        $response = new static();
        $response->data = $data;
        $response->type = $type ?: 'html';
        $response->code = $code;
        $response->header = $header;
        return $response;
    }
    
    public function send()
    {
        $this->sendHeader();
        $this->sendContent();
        return $this;
    }
    
    protected function sendHeader()
    {
        if (!headers_sent()) {
            http_response_code($this->code);
            
            foreach ($this->header as $name => $val) {
                header($name . ':' . $val);
            }
        }
    }
    
    protected function sendContent()
    {
        echo $this->data;
    }
    
    public function header($name, $value)
    {
        $this->header[$name] = $value;
        return $this;
    }
    
    public function data($data)
    {
        $this->data = $data;
        return $this;
    }
    
    public function code($code)
    {
        $this->code = $code;
        return $this;
    }
}
