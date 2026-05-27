<?php
define('ROOT_PATH', __DIR__ . '/backend/');

require __DIR__ . '/backend/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use think\facade\Db;

echo "=== 客户删除逻辑测试 ===\n\n";

$customerId = isset($argv[1]) ? (int)$argv[1] : 0;

if ($customerId === 0) {
    echo "用法: php test_delete.php <客户ID>\n\n";
    echo "示例: php test_delete.php 1\n\n";
    echo "=== 可用的客户列表 ===\n";
    
    $customers = Db::name('customer')
        ->field('customer_id, name')
        ->order('customer_id', 'desc')
        ->limit(10)
        ->select()
        ->toArray();
    
    foreach ($customers as $c) {
        echo "客户ID: {$c['customer_id']}, 名称: {$c['name']}\n";
    }
    
    exit;
}

echo "测试客户ID: {$customerId}\n\n";

$customer = Db::name('customer')->where('customer_id', $customerId)->find();

if (!$customer) {
    echo "错误: 客户不存在\n";
    exit;
}

echo "客户名称: {$customer['name']}\n";

$hasOrders = Db::name('order')->where('customer_id', $customerId)->whereNull('delete_time')->count();
echo "订单数量: {$hasOrders}\n";

$hasContacts = Db::name('contact')->where('customer_id', $customerId)->count();
echo "联系人数量: {$hasContacts}\n\n";

if ($hasContacts > 0) {
    echo "联系人列表:\n";
    $contacts = Db::name('contact')->where('customer_id', $customerId)->select()->toArray();
    foreach ($contacts as $c) {
        echo "  - ID: {$c['contact_id']}, 姓名: {$c['name']}, 电话: {$c['mobile']}\n";
    }
    echo "\n";
}

echo "=== 检查删除逻辑 ===\n";

// 直接检查删除逻辑，不调用 Service（避免权限检查）
if ($hasOrders > 0) {
    echo "✓ 订单检查: 该客户存在 {$hasOrders} 个订单，应该禁止删除\n";
    $expectedResult = [
        'code' => 1,
        'msg' => '该客户存在订单，无法删除',
        'data' => null
    ];
    echo "预期返回: " . json_encode($expectedResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else if ($hasContacts > 0) {
    echo "✓ 联系人检查: 该客户存在 {$hasContacts} 个联系人\n";
    echo "✓ 预期返回 code=201，前端应该弹出确认框\n";
    
    $expectedResult = [
        'code' => 201,
        'msg' => '该客户存在联系人，确认删除将同时删除所有联系人',
        'data' => ['contact_count' => $hasContacts]
    ];
    echo "预期返回: " . json_encode($expectedResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else {
    echo "✓ 没有订单和联系人，预期直接删除成功\n";
    $expectedResult = [
        'code' => 200,
        'msg' => '客户删除成功',
        'data' => null
    ];
    echo "预期返回: " . json_encode($expectedResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== 检查服务器代码是否是最新版本 ===\n";

$csFile = __DIR__ . '/backend/app/service/CustomerService.php';
echo "文件: {$csFile}\n";
echo "修改时间: " . date('Y-m-d H:i:s', filemtime($csFile)) . "\n\n";

$csCode = file_get_contents($csFile);
if (strpos($csCode, "code' => 201") !== false) {
    echo "✓ 服务器代码包含删除确认逻辑\n";
} else {
    echo "✗ 服务器代码不包含删除确认逻辑，需要更新！\n";
}

if (strpos($csCode, "whereNull('delete_time'") !== false) {
    echo "✓ 服务器代码包含订单过滤（排除已删除订单）\n";
} else {
    echo "✗ 服务器代码不包含订单过滤，需要更新！\n";
}

echo "\n=== 结论 ===\n";
if ($hasOrders > 0) {
    echo "该客户有订单，应该禁止删除。\n";
    echo "请选择一个没有订单的客户进行测试。\n";
} else if ($hasContacts > 0) {
    echo "该客户有联系人，删除时应该弹出确认框。\n";
    echo "请确保服务器代码是最新版本！\n";
} else {
    echo "该客户没有订单和联系人，应该直接删除。\n";
}
