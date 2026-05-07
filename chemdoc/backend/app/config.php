<?php
return [
    'default_return_type' => 'json',
    'default_ajax_return' => 'json',
    'exception_handle' => \app\exception\ExceptionHandle::class,
    'app_debug' => env('APP_DEBUG', true),
    'default_timezone' => 'Asia/Shanghai',
];
