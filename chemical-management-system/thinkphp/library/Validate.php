<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------

namespace think;

class Validate
{
    protected $rule = [];
    protected $message = [];
    protected $error = [];
    
    public function rule($name, $rule = '')
    {
        if (is_array($name)) {
            $this->rule = array_merge($this->rule, $name);
        } else {
            $this->rule[$name] = $rule;
        }
        return $this;
    }
    
    public function message($name, $message = '')
    {
        if (is_array($name)) {
            $this->message = array_merge($this->message, $name);
        } else {
            $this->message[$name] = $message;
        }
        return $this;
    }
    
    public function check($data)
    {
        foreach ($this->rule as $name => $rule) {
            if (!isset($data[$name])) {
                if (strpos($rule, 'require') === false) {
                    $this->error[$name] = $this->message[$name] ?? "{$name}不能为空";
                    continue;
                }
            }
            
            $value = $data[$name] ?? '';
            $rules = explode('|', $rule);
            
            foreach ($rules as $r) {
                if (strpos($r, ':')) {
                    list($ruleName, $ruleParam) = explode(':', $r);
                } else {
                    $ruleName = $r;
                    $ruleParam = '';
                }
                
                $ruleMethod = 'check' . ucfirst($ruleName);
                
                if (method_exists($this, $ruleMethod)) {
                    if (!$this->$ruleMethod($value, $ruleParam)) {
                        $this->error[$name] = $this->message[$name] ?? "{$name}验证失败";
                        break;
                    }
                }
            }
        }
        
        return empty($this->error);
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    protected function checkRequire($value)
    {
        return !empty($value) || $value === '0';
    }
    
    protected function checkAlpha($value)
    {
        return preg_match('/^[a-zA-Z]+$/', $value);
    }
    
    protected function checkNumber($value)
    {
        return is_numeric($value);
    }
    
    protected function checkEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }
    
    protected function checkUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }
    
    protected function checkMobile($value)
    {
        return preg_match('/^1[3-9]\d{9}$/', $value);
    }
    
    protected function checkLength($value, $length)
    {
        $length = explode(',', $length);
        $len = mb_strlen($value);
        if (count($length) == 1) {
            return $len == $length[0];
        }
        return $len >= $length[0] && $len <= $length[1];
    }
    
    protected function checkMin($value, $min)
    {
        return $value >= $min;
    }
    
    protected function checkMax($value, $max)
    {
        return $value <= $max;
    }
    
    protected function checkIn($value, $param)
    {
        $values = explode(',', $param);
        return in_array($value, $values);
    }
    
    protected function checkBetween($value, $param)
    {
        return $this->checkMin($value, explode(',', $param)[0]) && 
               $this->checkMax($value, explode(',', $param)[1]);
    }
}
