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

class Controller
{
    protected $request;
    
    public function __construct()
    {
        $this->request = Request::instance();
    }
    
    protected function fetch($template = '', $vars = [], $replace = [])
    {
        return View::instance($vars, $replace)->fetch($template);
    }
    
    protected function assign($name, $value = '')
    {
        return View::instance()->assign($name, $value);
    }
    
    protected function redirect($url, $params = [], $code = 302)
    {
        return Response::create($url, 'redirect', $code);
    }
    
    protected function success($msg, $url = null, $data = '', $wait = 3)
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        
        if (Request::instance()->isAjax()) {
            return json($result);
        }
        
        $this->assign('msg', $msg);
        $this->assign('url', $url);
        $this->assign('wait', $wait);
        
        return $this->fetch(config('dispatch_success_tmpl'));
    }
    
    protected function error($msg, $url = null, $data = '', $wait = 3)
    {
        if (is_null($url)) {
            $url = 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Url::build($url);
        }
        
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        
        if (Request::instance()->isAjax()) {
            return json($result);
        }
        
        $this->assign('msg', $msg);
        $this->assign('url', $url);
        $this->assign('wait', $wait);
        
        return $this->fetch(config('dispatch_error_tmpl'));
    }
    
    public function __call($method, $args)
    {
        return $this->error('方法不存在');
    }
}
