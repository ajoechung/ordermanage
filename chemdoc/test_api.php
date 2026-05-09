<?php
// 测试 API 接口
$baseUrl = 'http://szy.ajoe.cn/api';

// 测试登录
echo "=== 测试登录接口 ===\n";
$loginData = json_encode(['username' => 'admin', 'password' => '123456', 'captcha' => 'test', 'captcha_key' => 'test']);
$loginResult = httpPost($baseUrl . '/login', $loginData);
echo "登录结果: " . $loginResult . "\n\n";

// 解析 token
$loginJson = json_decode($loginResult, true);
$token = $loginJson['data']['token'] ?? '';

if ($token) {
    echo "=== 测试客户列表接口 ===\n";
    $customerResult = httpGet($baseUrl . '/customer?page=1&limit=10', $token);
    echo "客户列表: " . $customerResult . "\n\n";
    
    echo "=== 测试产品列表接口 ===\n";
    $productResult = httpGet($baseUrl . '/product?page=1&limit=10', $token);
    echo "产品列表: " . $productResult . "\n\n";
    
    echo "=== 测试供应商列表接口 ===\n";
    $supplierResult = httpGet($baseUrl . '/supplier?page=1&limit=10', $token);
    echo "供应商列表: " . $supplierResult . "\n\n";
    
    echo "=== 测试订单列表接口 ===\n";
    $orderResult = httpGet($baseUrl . '/order?page=1&limit=10', $token);
    echo "订单列表: " . $orderResult . "\n\n";
}

function httpGet($url, $token = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $headers = [];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return 'CURL错误: ' . $error;
    }
    return $result;
}

function httpPost($url, $data, $token = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    $headers = [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return 'CURL错误: ' . $error;
    }
    return $result;
}
?>