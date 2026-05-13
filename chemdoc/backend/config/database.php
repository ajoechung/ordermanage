<?php
$envPath = dirname(__DIR__) . '/.env';
$envConfig = [];

if (is_file($envPath)) {
    $envConfig = parse_ini_file($envPath, true);
}

$dbConfig = $envConfig['DATABASE'] ?? [];

return [
    'type'            => $dbConfig['TYPE'] ?? 'mysql',
    'hostname'        => $dbConfig['HOSTNAME'] ?? '127.0.0.1',
    'database'        => $dbConfig['DATABASE'] ?? 'chemdoc',
    'username'        => $dbConfig['USERNAME'] ?? 'root',
    'password'        => $dbConfig['PASSWORD'] ?? 'root',
    'hostport'        => $dbConfig['HOSTPORT'] ?? '3306',
    'charset'         => $dbConfig['CHARSET'] ?? 'utf8mb4',
    'prefix'          => $dbConfig['PREFIX'] ?? '',
    'debug'           => ($dbConfig['DEBUG'] ?? 'true') === 'true',
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
