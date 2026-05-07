<?php

// +----------------------------------------------------------------------
// | 静态文件配置
// +----------------------------------------------------------------------
return [
    'view_replace_str' => [
        '__PUBLIC__' => '/public',
        '__ADMIN__' => '/public/admin',
    ],
    
    'template' => [
        'view_path' => 'view/',
        'tpl_begin' => '{',
        'tpl_end' => '}',
        'taglib_begin' => '{',
        'taglib_end' => '}',
        'taglib_load' => true,
        'tpl_cache' => true,
    ],
    
    'url_route_on' => true,
    'url_route_must' => false,
    'route_config_file' => ['route'],
    
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
];
