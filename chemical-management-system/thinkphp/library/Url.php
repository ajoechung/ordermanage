<?php
// +----------------------------------------------------------------------

namespace think;

class Url
{
    public static function build($url = '', $vars = '', $domain = false)
    {
        if (strpos($url, '://') !== false) {
            return $url;
        }
        
        $request = Request::instance();
        
        if ($url === '') {
            return $request->baseUrl();
        }
        
        if (strpos($url, '/') === false) {
            $controller = $request->param('_c', 'index');
            $action = $url;
            $url = $controller . '/' . $action;
        }
        
        $url = '/' . ltrim($url, '/');
        
        if ($vars) {
            if (is_array($vars)) {
                $vars = http_build_query($vars);
            }
            $url .= '?' . $vars;
        }
        
        return $url;
    }
}
