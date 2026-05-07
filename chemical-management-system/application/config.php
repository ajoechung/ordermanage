<?php

return [
    'default_return_type' => 'html',
    'default_ajax_return' => 'json',
    'default_jsonp_handler' => 'jsonpReturn',
    'var_jsonp_handler' => 'callback',
    
    'exception_handle' => '\\app\\common\\library\\ExceptionHandle',
    
    'show_error_msg' => false,
    'exception_tmpl' => APP_PATH . 'tpl' . DS . 'think_exception.tpl',
    
    'default_filter' => '',
    
    'default_validate' => true,
    
    'auto_bind_model' => true,
    
    'class_suffix' => false,
    
    'controller_suffix' => false,
    
    'url_domain_deploy' => false,
    'url_domain_root' => '',
    
    'url_controller_layer' => 'controller',
    'url_action_suffix' => '',
    'url_param_type' => 0,
    'url_lazy_route' => false,
    'url_route_on' => true,
    'url_route_must' => false,
    'route_config_file' => ['route'],
    'route_complete_match' => false,
    'route_action_suffix' => 'Action',
    
    'template_engine' => 'Think',
    'template_type' => 'php',
    
    'template_view_path' => 'view/',
    'template_cache_path' => RUNTIME_PATH . 'temp' . DS,
    'template_cache_on' => true,
    'template_compile_cache_on' => true,
    'template_compile_cache_path' => RUNTIME_PATH . 'temp' . DS,
    'taglib_begin' => '{',
    'taglib_end' => '}',
    'taglib_load' => true,
    'taglib_build_in' => 'cx',
    'tpl_begin' => '{',
    'tpl_end' => '}',
    
    'view_replace_str' => [
        '__PUBLIC__' => '/public',
        '__ADMIN__' => '/public/admin',
    ],
    
    'session' => [
        'prefix' => 'chemical',
        'type' => '',
        'auto_start' => true,
        'httponly' => true,
        'secure' => false,
    ],
    
    'cookie' => [
        'prefix' => 'chemical_',
        'expire' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    
    'paginate' => [
        'type' => 'bootstrap',
        'var_page' => 'page',
        'list_rows' => 15,
    ],
    
    'app_debug' => true,
    'app_trace' => false,
    
    'default_timezone' => 'Asia/Shanghai',
    
    'url_html_suffix' => 'html',
];
