<?php
namespace app;

use think\App;
use think\exception\Handle;
use think\exception\ValidateException;
use think\Response;
use app\exception\BusinessException;
use app\service\Result;

defined('APP_DEBUG') or define('APP_DEBUG', env('app.debug', false));

return [
    'app_debug'               => env('app.debug', false),
    'app_trace'              => env('app.trace', false),
    'default_timezone'       => env('app.default_timezone', 'Asia/Shanghai'),
    'default_return_type'    => 'json',
    'default_ajax_return'    => 'json',
    'default_jsonp_handler'  => 'jsonpReturn',
    'var_jsonp_handler'       => 'callback',
    'exception_handle'       => Handle::class,
    'show_error_msg'         => true,
    'show_detail_msg'        => true,
    'exception_tmpl'         => app()->getThinkPath() . 'tpl/think_exception.tpl',
    'exception_tmpl_replace'  => [],
    'url_route_on'           => true,
    'url_route_must'         => false,
    'route_annotation'        => false,
    'url_domain_deploy'       => false,
    'url_domain_root'        => '',
    'url_controller_layer'    => 'controller',
    'controller_suffix'       => false,
    'action_suffix'          => '',
    'action_suffix'          => '',
    'default_filter'         => '',
    'default_validate'       => true,
    'auto_bind_model'        => true,
    'root_namespace'         => [],
    'request_cache'          => false,
    'request_cache_expire'   => null,
    'request_cache_except'   => [],
    'default_lang'           => 'zh-cn',
    'lang_switch_on'         => false,
    'lang_detect_var'        => 'lang',
    'default_return_type'     => 'json',
    'default_ajax_return'     => 'json',
    'strict_model_fields'    => true,
    'datetime_format'        => 'Y-m-d H:i:s',
    'datetime_format_v2'     => 'Y-m-d H:i:s',
    'default_pagination'      => [
        'list_rows' => 20,
        'var_page'  => 'page',
    ],
    'display_error_trace'    => true,
    'http_exception_template' => [],
];
