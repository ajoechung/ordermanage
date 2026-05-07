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

// 系统助手函数

if (!function_exists('dump')) {
    function dump($var, $echo = true, $label = null, $flags = ENT_SUBSTITUTE)
    {
        $label = (null === $label) ? '' : rtrim($label) . ':';
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $label . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, $flags);
            }
            $output = '<pre>' . $label . $output . '</pre>';
        }
        if ($echo) {
            echo($output);
            return null;
        } else {
            return $output;
        }
    }
}

if (!function_exists('db')) {
    function db($name = '', $config = [], $force = false)
    {
        static $_db = [];
        $guid = (is_array($config) ? md5(serialize($config)) : $config) . $name;
        if (!isset($_db[$guid]) || $force) {
            $_db[$guid] = Db::connect($config)->name($name);
        }
        return $_db[$guid];
    }
}

if (!function_exists('session')) {
    function session($name = '', $value = '')
    {
        if (empty($value)) {
            if ('' === $name) {
                return \think\Session::get();
            } elseif (is_null($name)) {
                return \think\Session::delete();
            } else {
                return \think\Session::get($name);
            }
        } else {
            return \think\Session::set($name, $value);
        }
    }
}

if (!function_exists('cache')) {
    function cache($name = '', $value = '', $options = null)
    {
        if (empty($value)) {
            if ('' === $name) {
                return \think\Cache::get();
            } elseif (is_null($name)) {
                return \think\Cache::clear();
            } else {
                return \think\Cache::get($name);
            }
        } elseif (is_null($value)) {
            return \think\Cache::rm($name);
        } else {
            return \think\Cache::set($name, $value, $options);
        }
    }
}

if (!function_exists('config')) {
    function config($name = '', $value = null)
    {
        if (is_null($value)) {
            if ('' === $name) {
                return \think\Config::get();
            } else {
                return \think\Config::get($name);
            }
        } else {
            return \think\Config::set($name, $value);
        }
    }
}

if (!function_exists('input')) {
    function input($name = '', $default = null, $filter = '')
    {
        if (strpos($name, '/')) {
            list($name, $type) = explode('/', $name, 2);
        } else {
            $type = 's';
        }
        if ($name === '') {
            return \think\Input::getInput();
        } elseif (strpos($name, '.')) {
            $params = explode('.', $name);
            return call_user_func_array([\think\Input::instance(), $params[0]], array_slice($params, 1));
        } else {
            return \think\Input::instance()->$type($name, $default, $filter);
        }
    }
}

if (!function_exists('url')) {
    function url($url = '', $vars = '', $domain = false)
    {
        return \think\Url::build($url, $vars, $domain);
    }
}

if (!function_exists('view')) {
    function view($template = '', $vars = [], $replace = [], $code = 200)
    {
        return \think\View::instance($vars, $replace)-> fetch($template, [], [], $code);
    }
}

if (!function_exists('json')) {
    function json($data = [], $code = 200, $header = [], $options = 0)
    {
        return \think\Response::create($data, 'json', $code, $header, $options);
    }
}

if (!function_exists('redirect')) {
    function redirect($url = '', $params = [], $code = 302, $with = [], $schema = '', $domain = false)
    {
        if ($with) {
            session('flash', $with);
        }
        $url = url($url, $params, $domain) ?: $url;
        return \think\Response::create($url, 'redirect', $code);
    }
}

if (!function_exists('request')) {
    function request()
    {
        return \think\Request::instance();
    }
}

if (!function_exists('app')) {
    function app($name = '')
    {
        if ($name) {
            return \think\App::instance($name);
        }
        return \think\App::instance();
    }
}

if (!function_exists('container')) {
    function container($name = '')
    {
        return \think\Container::getInstance()->make($name ?: 'think\App');
    }
}

if (!function_exists('bind')) {
    function bind($abstract, $concrete = null)
    {
        if ($concrete instanceof \Closure) {
            \think\Container::getInstance()->bind($abstract, $concrete);
        } elseif (is_object($concrete)) {
            \think\Container::getInstance()->instance($abstract, $concrete);
        } else {
            \think\Container::getInstance()->bind($abstract, $concrete);
        }
    }
}

if (!function_exists('invoke')) {
    function invoke($class, $method = null, $vars = [])
    {
        if ($class instanceof \Closure) {
            return Container::getInstance()->invokeFunction($class, $vars);
        }
        return Container::getInstance()->invoke($class, $method, $vars);
    }
}

if (!function_exists('log_write')) {
    function log_write($message, $type = 'log', $destination = '')
    {
        \think\Log::write($message, $type, $destination);
    }
}

if (!function_exists('load_trait')) {
    function load_trait($trait, $class = '')
    {
        $trait = str_replace('@', '\\traits\\', $trait);
        if ($class) {
            return class_exists($class, false) ? true : class_alias($trait, $class);
        }
        return class_alias($trait, $trait);
    }
}

if (!function_exists('class_alias')) {
    function class_alias($alias, $class)
    {
        if (!class_exists($class, false)) {
            class_alias($alias, $class);
        }
        return true;
    }
}

if (!function_exists('is_android')) {
    function is_android()
    {
        return \think\Agent::isAndroid();
    }
}

if (!function_exists('is_ios')) {
    function is_ios()
    {
        return \think\Agent::isIOS();
    }
}

if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        return \think\Agent::isMobile();
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip($type = 0, $adv = true)
    {
        return \think\Request::instance()->ip($type, $adv);
    }
}
