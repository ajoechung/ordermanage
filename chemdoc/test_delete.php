<?php
define('ROOT_PATH', __DIR__ . '/backend/');

require __DIR__ . '/backend/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use app\service\CustomerService;
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

echo "=== 模拟删除接口调用 (force=false) ===\n";
$service = new CustomerService();
$result = $service->delete($customerId, false);

echo "返回结果:\n";
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

if ($result['code'] === 201) {
    echo "✓ 后端正确返回 code=201，应该弹出确认框\n";
    echo "✓ 联系人数量: {$result['data']['contact_count']}\n\n";
    
    echo "=== 模拟确认后的删除接口调用 (force=true) ===\n";
    $result2 = $service->delete($customerId, true);
    echo "返回结果:\n";
    echo json_encode($result2, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
} else if ($result['code'] === 200) {
    echo "✓ 后端返回 code=200，直接删除成功\n";
} else {
    echo "✗ 后端返回 code={$result['code']}，错误: {$result['msg']}\n";
}
