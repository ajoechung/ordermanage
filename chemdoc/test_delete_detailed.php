<?php
define('ROOT_PATH', __DIR__ . '/backend/');

require __DIR__ . '/backend/vendor/autoload.php';

$app = new think\App();
$app->initialize();

use think\facade\Db;

echo "=== 客户删除逻辑深度测试 ===\n\n";

$customerId = isset($argv[1]) ? (int)$argv[1] : 45;

echo "测试客户ID: {$customerId}\n\n";

// 检查客户
$customer = Db::name('customer')->where('customer_id', $customerId)->find();
if (!$customer) {
    echo "错误: 客户不存在\n";
    exit;
}
echo "客户名称: {$customer['name']}\n";

// 检查订单
$hasOrders = Db::name('order')->where('customer_id', $customerId)->whereNull('delete_time')->count();
echo "订单数量: {$hasOrders}\n";

// 检查联系人
$hasContacts = Db::name('contact')->where('customer_id', $customerId)->count();
echo "联系人数量: {$hasContacts}\n\n";

// 显示联系人列表
if ($hasContacts > 0) {
    echo "联系人列表:\n";
    $contacts = Db::name('contact')->where('customer_id', $customerId)->select()->toArray();
    foreach ($contacts as $c) {
        echo "  - ID: {$c['contact_id']}, 姓名: {$c['name']}, 电话: {$c['mobile']}\n";
    }
    echo "\n";
}

echo "=== 模拟删除逻辑（不实际删除）===\n";
echo "检查顺序: 权限检查 -> 订单检查 -> 联系人检查\n\n";

// 模拟权限检查（跳过，因为测试脚本没有用户上下文）
echo "[权限检查] 跳过（测试模式）\n\n";

// 模拟订单检查
echo "[订单检查] ";
if ($hasOrders > 0) {
    echo "✗ 存在 {$hasOrders} 个订单\n";
    echo "  预期返回: {\"code\": 1, \"msg\": \"该客户存在订单，无法删除\"}\n";
    exit;
} else {
    echo "✓ 没有订单，可以继续\n\n";
}

// 模拟联系人检查
echo "[联系人检查] ";
if ($hasContacts > 0) {
    echo "✓ 存在 {$hasContacts} 个联系人\n";
    echo "  预期返回: {\"code\": 201, \"msg\": \"该客户存在联系人，确认删除将同时删除所有联系人\", \"data\": {\"contact_count\": {$hasContacts}}}\n";
    echo "\n[结论] 应该弹出确认框，让用户确认是否删除！\n";
} else {
    echo "✓ 没有联系人\n";
    echo "  预期返回: {\"code\": 200, \"msg\": \"客户删除成功\"}\n";
    echo "\n[结论] 直接删除是正确的\n";
}

echo "\n=== 检查服务器代码 ===";
echo "\n文件: backend/app/service/CustomerService.php\n";
echo "修改时间: " . date('Y-m-d H:i:s', filemtime(__DIR__ . '/backend/app/service/CustomerService.php')) . "\n\n";

// 检查代码中是否包含正确的逻辑
$csCode = file_get_contents(__DIR__ . '/backend/app/service/CustomerService.php');

if (strpos($csCode, "code' => 201") !== false) {
    echo "✓ 代码包含 \"code' => 201\" (联系人确认逻辑)\n";
} else {
    echo "✗ 代码不包含 \"code' => 201\"，需要更新！\n";
}

if (strpos($csCode, "contact_count") !== false) {
    echo "✓ 代码包含 \"contact_count\" (返回联系人数量)\n";
} else {
    echo "✗ 代码不包含 \"contact_count\"，需要更新！\n";
}

if (strpos($csCode, "whereNull('delete_time')") !== false) {
    echo "✓ 代码包含 \"whereNull('delete_time')\" (排除已删除订单)\n";
} else {
    echo "✗ 代码不包含 \"whereNull('delete_time')\"，需要更新！\n";
}

echo "\n=== 检查前端代码 ===";
echo "\n搜索 contact_count 是否存在于构建后的代码中:\n";

$distFiles = glob(__DIR__ . '/frontend/dist/assets/*.js');
$found = false;
foreach ($distFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'contact_count') !== false) {
        $found = true;
        echo "✓ 在 " . basename($file) . " 中找到 contact_count\n";
        break;
    }
}

if (!$found) {
    echo "✗ 构建后的前端代码中没有找到 contact_count，需要重新构建！\n";
}

echo "\n=== 最终建议 ===";
echo "\n1. 如果后端代码检查失败，请执行: git pull origin main";
echo "\n2. 如果前端代码检查失败，请执行: cp -r frontend/dist/* frontend-dist/";
echo "\n3. 如果都通过但还是直接删除，检查浏览器缓存（Ctrl+Shift+Del）\n";
?>
