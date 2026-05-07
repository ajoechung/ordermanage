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

// [ 框架基础文件 ]
defined('THINK_PATH') or define('THINK_PATH', __DIR__ . DS);
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('RUNTIME_PATH') or define('RUNTIME_PATH', APP_PATH . 'runtime' . DS);
defined('EXTEND_PATH') or define('EXTEND_PATH', THINK_PATH . 'extend' . DS);
defined('VENDOR_PATH') or define('VENDOR_PATH', THINK_PATH . 'vendor' . DS);
defined('CORE_PATH') or define('CORE_PATH', THINK_PATH . 'library' . DS);
defined('TRAIT_PATH') or define('TRAIT_PATH', THINK_PATH . 'library' . DS . 'traits' . DS);

defined('APP_DEBUG') or define('APP_DEBUG', false);
defined('RUNTIME_NOT_FILE') or define('RUNTIME_NOT_FILE', APP_PATH . 'runtime' . DS . '~' . str_replace('/', '_', $_SERVER['SCRIPT_NAME']) . '.php');

if (APP_DEBUG) {
    ini_set('display_errors', 'On');
} else {
    ini_set('display_errors', 'Off');
}

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// [ 加载框架底层基础文件 ]
require CORE_PATH . 'Think.php';
