<?php
use think\facade\Env;

return [
    'type'            => Env::get('database.type', 'mysql'),
    'hostname'        => Env::get('database.hostname', '127.0.0.1'),
    'database'        => Env::get('database.database', 'chemdoc'),
    'username'        => Env::get('database.username', 'root'),
    'password'        => Env::get('database.password', 'root'),
    'hostport'        => Env::get('database.hostport', '3306'),
    'charset'         => Env::get('database.charset', 'utf8mb4'),
    'prefix'          => Env::get('database.prefix', ''),
    'debug'           => Env::get('database.debug', true),
    'deploy'          => 0,
    'rw_separate'     => false,
    'master_num'      => 1,
    'slave_no'        => '',
    'fields_strict'   => true,
    'fields_cache'     => false,
    'trigger_sql'      => true,
    'break_reconnect'  => false,
    'break_match_str'  => [],
    'resultset_type'  => 'array',
    'auto_timestamp'   => false,
    'datetime_format' => 'Y-m-d H:i:s',
    'sql_explain'     => false,
    'query'           => '\\think\\db\\Query',
    'builder'         => '',
];
