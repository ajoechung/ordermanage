<?php
namespace app\service;

use app\model\OrderModel;
use app\model\OrderItemModel;
use app\model\OrderInvoiceModel;
use app\model\ProductModel;
use app\model\CustomerModel;
use app\model\OperationLogModel;
use think\facade\Db;
use think\facade\Filesystem;

class OrderService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $customerId = $params['customer_id'] ?? '';
        $orderStatus = $params['order_status'] ?? '';
        $invoiceStatus = $params['invoice_status'] ?? '';
        $hasInvoice = $params['has_invoice'] ?? '';
        $dateRange = $params['date_range'] ?? [];

        $query = OrderModel::with(['customer', 'createUser', 'invoices']);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($customerId)) {
            $query->scope('customerId', (int)$customerId);
        }

        if (!empty($orderStatus)) {
            $query->scope('orderStatus', (int)$orderStatus);
        }

        if (!empty($invoiceStatus)) {
            $query->scope('invoiceStatus', (int)$invoiceStatus);
        }

        if ($hasInvoice !== '') {
            if ($hasInvoice === '1') {
                // 有发票的订单
                $subQuery = \app\model\OrderInvoiceModel::field('order_id')->buildSql();
                $query->whereIn('order_id', $subQuery);
            } elseif ($hasInvoice === '0') {
                // 没有发票的订单
                $subQuery = \app\model\OrderInvoiceModel::field('order_id')->buildSql();
                $query->whereNotIn('order_id', $subQuery);
            }
        }

        if (!empty($dateRange) && is_array($dateRange)) {
            $query->scope('dateRange', $dateRange);
        }

        $total = $query->count();
        $list = $query->order('order_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['customer'])) {
                $item['customer_name'] = $item['customer']['name'] ?? '';
                unset($item['customer']);
            }
            if (isset($item['create_user'])) {
                $item['create_name'] = $item['create_user']['realname'] ?? '';
                unset($item['create_user']);
            }
            // 判断是否有发票
            $item['has_invoice'] = !empty($item['invoices']) ? 1 : 0;
            unset($item['invoices']);
            $item['total_amount'] = round((float)$item['total_amount'], 2);
            $item['actual_amount'] = round((float)$item['actual_amount'], 2);
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        try {
            $order = OrderModel::with(['customer', 'contact', 'createUser'])->find($id);

            if (!$order) {
                return Result::notFound('订单不存在');
            }

            $data = $order->toArray();

            if (isset($data['customer'])) {
                $data['customer_name'] = $data['customer']['name'] ?? '';
                unset($data['customer']);
            }

            // 优先使用订单表中直接存储的联系人信息
            if (empty($data['contact_name'])) {
                if (isset($data['contact'])) {
                    $data['contact_name'] = $data['contact']['name'] ?? '';
                    $data['contact_phone'] = $data['contact']['phone'] ?? '';
                    unset($data['contact']);
                }
            } else {
                if (isset($data['contact'])) {
                    unset($data['contact']);
                }
            }

            if (isset($data['create_user'])) {
                $data['create_name'] = $data['create_user']['realname'] ?? '';
                unset($data['create_user']);
            }

            // 手动查询订单明细
            $items = OrderItemModel::where('order_id', $id)->select()->toArray();
            $data['items'] = [];

            if (!empty($items)) {
                foreach ($items as $item) {
                    $product = ProductModel::find($item['product_id']);
                    $item['product_name'] = $product ? $product['name'] : '';
                    $item['product_spec'] = $product ? $product['spec'] : '';
                    $item['unit_price'] = $item['unit_price'] ?? 0;
                    $item['quantity'] = $item['quantity'] ?? 0;
                    $item['subtotal'] = $item['subtotal'] ?? 0;
                    $data['items'][] = $item;
                }
            }

            $data['total_amount'] = round((float)($data['total_amount'] ?? 0), 2);
            $data['actual_amount'] = round((float)($data['actual_amount'] ?? 0), 2);

            return Result::success($data);
        } catch (\Exception $e) {
            // 如果关联查询失败，尝试只查询订单基本信息
            $order = OrderModel::find($id);
            if (!$order) {
                return Result::notFound('订单不存在');
            }
            $data = $order->toArray();
            // 即使在 catch 块中也手动查询订单明细
            $items = OrderItemModel::where('order_id', $id)->select()->toArray();
            $data['items'] = [];
            if (!empty($items)) {
                foreach ($items as $item) {
                    $product = ProductModel::find($item['product_id']);
                    $item['product_name'] = $product ? $product['name'] : '';
                    $item['product_spec'] = $product ? $product['spec'] : '';
                    $item['unit_price'] = $item['unit_price'] ?? 0;
                    $item['quantity'] = $item['quantity'] ?? 0;
                    $item['subtotal'] = $item['subtotal'] ?? 0;
                    $data['items'][] = $item;
                }
            }
            return Result::success($data);
        }
    }

    public function create(array $data): array
    {
        Db::startTrans();
        try {
            // 计算订单总额
            $totalAmount = 0;
            $items = [];
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $unitPrice = floatval($item['unit_price'] ?? 0);
                    $quantity = intval($item['quantity'] ?? 1);
                    $subtotal = $unitPrice * $quantity;
                    $totalAmount += $subtotal;
                    $items[] = array_merge($item, ['subtotal' => $subtotal]);
                }
            }

            $actualAmount = $totalAmount - floatval($data['discount_amount'] ?? 0);

            $order = OrderModel::create([
                'order_no' => $this->generateOrderNo(),
                'customer_id' => $data['customer_id'],
                'contact_id' => $data['contact_id'] ?? 0,
                'contact_name' => $data['contact_name'] ?? '',
                'contact_phone' => $data['contact_phone'] ?? '',
                'total_amount' => $totalAmount,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'actual_amount' => $actualAmount,
                'order_time' => $data['order_time'] ?? date('Y-m-d H:i:s'),
                'expect_delivery_date' => $data['expect_delivery_date'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? '',
                'remark' => $data['remark'] ?? '',
                'create_user_id' => request()->user_id ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($items)) {
                foreach ($items as $item) {
                    OrderItemModel::create([
                        'order_id' => $order->order_id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'product_spec' => $item['product_spec'] ?? '',
                        'product_unit' => $item['product_unit'] ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $item['subtotal'],
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            Db::commit();

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '订单管理',
                '新增',
                '新增订单：' . $order->order_no
            );

            return Result::success(['order_id' => $order->order_id], '订单创建成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Result::error('订单创建失败：' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): array
    {
        $order = OrderModel::find($id);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        if ($order->order_status > 1) {
            return Result::error('已确认的订单无法修改');
        }

        Db::startTrans();
        try {
            // 计算订单总额
            $totalAmount = 0;
            $items = [];
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $unitPrice = floatval($item['unit_price'] ?? 0);
                    $quantity = intval($item['quantity'] ?? 1);
                    $subtotal = $unitPrice * $quantity;
                    $totalAmount += $subtotal;
                    $items[] = array_merge($item, ['subtotal' => $subtotal]);
                }
            }

            $actualAmount = $totalAmount - floatval($data['discount_amount'] ?? 0);

            $updateData = [
                'customer_id' => $data['customer_id'] ?? $order->customer_id,
                'contact_id' => $data['contact_id'] ?? $order->contact_id,
                'contact_name' => $data['contact_name'] ?? $order->contact_name ?? '',
                'contact_phone' => $data['contact_phone'] ?? $order->contact_phone ?? '',
                'total_amount' => $totalAmount,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'actual_amount' => $actualAmount,
                'expect_delivery_date' => $data['expect_delivery_date'] ?? $order->expect_delivery_date,
                'delivery_address' => $data['delivery_address'] ?? $order->delivery_address,
                'remark' => $data['remark'] ?? '',
                'update_time' => date('Y-m-d H:i:s'),
            ];

            $order->save($updateData);

            if (!empty($items)) {
                OrderItemModel::where('order_id', $id)->delete();
                foreach ($items as $item) {
                    OrderItemModel::create([
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'product_spec' => $item['product_spec'] ?? '',
                        'product_unit' => $item['product_unit'] ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $item['subtotal'],
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            Db::commit();

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '订单管理',
                '编辑',
                '编辑订单：' . $order->order_no
            );

            return Result::success(null, '订单更新成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Result::error('订单更新失败：' . $e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $order = OrderModel::find($id);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        if ($order->order_status > 1) {
            return Result::error('已确认的订单无法删除');
        }

        OrderItemModel::where('order_id', $id)->delete();
        $order->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '订单管理',
            '删除',
            '删除订单：' . $order->order_no
        );

        return Result::success(null, '订单删除成功');
    }

    public function updateStatus(int $id, int $status): array
    {
        $order = OrderModel::find($id);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        Db::name('order')->where('order_id', $id)->update([
            'order_status' => $status,
            'update_time' => date('Y-m-d H:i:s')
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '订单管理',
            '状态变更',
            '订单 ' . $order->order_no . ' 状态变更为：' . $this->getStatusText($status)
        );

        return Result::success(null, '状态更新成功');
    }

    protected function generateOrderNo(): string
    {
        return 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected function getStatusText(int $status): string
    {
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已完成',
            5 => '已取消',
        ];
        return $statusMap[$status] ?? '未知';
    }

    public function getInvoiceList(int $orderId): array
    {
        $order = OrderModel::find($orderId);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        $invoices = OrderInvoiceModel::where('order_id', $orderId)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        foreach ($invoices as &$invoice) {
            $invoice['file_size'] = $this->formatFileSize($invoice['file_size'] ?? 0);
        }

        return Result::success($invoices);
    }

    public function uploadInvoice(): array
    {
        $orderId = request()->post('order_id', 0, 'intval');
        if ($orderId == 0) {
            $orderId = request()->param('order_id', 0, 'intval');
        }
        if ($orderId == 0) {
            return Result::validateError('订单ID不能为空');
        }

        $order = OrderModel::find($orderId);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        try {
            if (empty($_FILES['file'])) {
                return Result::validateError('请选择要上传的文件');
            }

            $uploadFile = $_FILES['file'];
            if ($uploadFile['error'] !== UPLOAD_ERR_OK) {
                return Result::error('文件上传失败，错误码：' . $uploadFile['error']);
            }

            $allowedExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $fileExt = strtolower(pathinfo($uploadFile['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExt)) {
                return Result::validateError('文件格式不支持');
            }

            if ($uploadFile['size'] > 52428800) {
                return Result::validateError('文件大小不能超过50MB');
            }

            $uploadPath = public_path() . '/uploads/invoices/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $saveName = md5(uniqid((string)mt_rand(), true)) . '.' . $fileExt;
            $targetPath = $uploadPath . $saveName;
            
            if (!move_uploaded_file($uploadFile['tmp_name'], $targetPath)) {
                return Result::error('文件保存失败');
            }

            $filePath = '/uploads/invoices/' . $saveName;
            $invoiceId = 0;
            
            try {
                $invoice = OrderInvoiceModel::create([
                    'order_id' => $orderId,
                    'file_name' => $uploadFile['name'],
                    'file_path' => $filePath,
                    'file_size' => $uploadFile['size'],
                    'file_type' => $uploadFile['type'],
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
                $invoiceId = $invoice->invoice_id;
            } catch (\Exception $e) {
                // 即使数据库操作失败，只要文件上传成功也算成功
            }
            
            try {
                OperationLogModel::log(
                    request()->user_id ?? 0,
                    request()->username ?? '',
                    '订单管理',
                    '上传发票',
                    '订单 ' . $order->order_no . ' 上传发票：' . $uploadFile['name']
                );
            } catch (\Exception $e) {
                // 日志失败不影响主流程
            }

            return Result::success(['invoice_id' => $invoiceId], '上传成功');
        } catch (\Exception $e) {
            return Result::error('上传失败：' . $e->getMessage());
        }
    }

    public function deleteInvoice(int $id): array
    {
        $invoice = OrderInvoiceModel::find($id);
        if (!$invoice) {
            return Result::notFound('发票不存在');
        }

        $order = OrderModel::find($invoice->order_id);

        if ($invoice->file_path) {
            $filePath = public_path() . $invoice->file_path;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $invoice->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '订单管理',
            '删除发票',
            '订单 ' . ($order->order_no ?? '') . ' 删除发票：' . $invoice->file_name
        );

        return Result::success(null, '删除成功');
    }

    protected function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / (1024 * 1024), 2) . ' MB';
        }
    }
}
