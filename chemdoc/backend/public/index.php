<?php
namespace app;

require_once __DIR__ . '/vendor/autoload.php';

$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
