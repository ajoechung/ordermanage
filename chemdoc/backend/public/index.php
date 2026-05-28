<?php
require_once __DIR__ . '/../vendor/autoload.php';

$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    $env = parse_ini_file($envPath, true);
    foreach ($env as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $envKey = strtoupper(str_replace('.', '_', "{$key}.{$k}"));
                putenv("{$envKey}={$v}");
                $_ENV[$envKey] = $v;
                $_SERVER[$envKey] = $v;
            }
        } else {
            $envKey = strtoupper(str_replace('.', '_', $key));
            putenv("{$envKey}={$value}");
            $_ENV[$envKey] = $value;
            $_SERVER[$envKey] = $value;
        }
    }
}

$http = (new think\App())->http;

$response = $http->run();

$response->send();

$http->end($response);
