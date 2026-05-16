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
        $dateRange = $params['date_range'] ?? [];

        $query = OrderModel::with(['customer', 'createUser']);

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
            $item['total_amount'] = round((float)$item['total_amount'], 2);
            $item['actual_amount'] = round((float)$item['actual_amount'], 2);
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        try {
            $order = OrderModel::with(['customer', 'contact', 'createUser', 'items.product'])->find($id);

            if (!$order) {
                return Result::notFound('订单不存在');
            }

            $data = $order->toArray();

            if (isset($data['customer'])) {
                $data['customer_name'] = $data['customer']['name'] ?? '';
                unset($data['customer']);
            }

            if (isset($data['contact'])) {
                $data['contact_name'] = $data['contact']['name'] ?? '';
                $data['contact_phone'] = $data['contact']['phone'] ?? '';
                unset($data['contact']);
            }

            if (isset($data['create_user'])) {
                $data['create_name'] = $data['create_user']['realname'] ?? '';
                unset($data['create_user']);
            }

            if (isset($data['items'])) {
                foreach ($data['items'] as &$item) {
                    if (isset($item['product'])) {
                        $item['product_name'] = $item['product']['name'] ?? '';
                        $item['product_code'] = $item['product']['code'] ?? '';
                        unset($item['product']);
                    }
                    // 确保字段存在
                    $item['unit_price'] = $item['unit_price'] ?? 0;
                    $item['quantity'] = $item['quantity'] ?? 0;
                    $item['subtotal'] = $item['subtotal'] ?? 0;
                }
            } else {
                $data['items'] = [];
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
            $data['items'] = [];
            return Result::success($data);
        }
    }

    public function create(array $data): array
    {
        Db::startTrans();
        try {
            $order = OrderModel::create([
                'order_no' => $this->generateOrderNo(),
                'customer_id' => $data['customer_id'],
                'contact_id' => $data['contact_id'] ?? 0,
                'total_amount' => $data['total_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'actual_amount' => $data['actual_amount'] ?? $data['total_amount'] ?? 0,
                'order_time' => $data['order_time'] ?? date('Y-m-d H:i:s'),
                'expect_delivery_date' => $data['expect_delivery_date'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? '',
                'remark' => $data['remark'] ?? '',
                'create_user_id' => request()->user_id ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    OrderItemModel::create([
                        'order_id' => $order->order_id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'product_spec' => $item['product_spec'] ?? '',
                        'product_unit' => $item['product_unit'] ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $item['subtotal'] ?? 0,
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
            $updateData = [
                'customer_id' => $data['customer_id'] ?? $order->customer_id,
                'contact_id' => $data['contact_id'] ?? $order->contact_id,
                'total_amount' => $data['total_amount'] ?? $order->total_amount,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'actual_amount' => $data['actual_amount'] ?? $order->actual_amount,
                'expect_delivery_date' => $data['expect_delivery_date'] ?? $order->expect_delivery_date,
                'delivery_address' => $data['delivery_address'] ?? $order->delivery_address,
                'remark' => $data['remark'] ?? '',
                'update_time' => date('Y-m-d H:i:s'),
            ];

            $order->save($updateData);

            if (isset($data['items']) && is_array($data['items'])) {
                OrderItemModel::where('order_id', $id)->delete();
                foreach ($data['items'] as $item) {
                    OrderItemModel::create([
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'product_spec' => $item['product_spec'] ?? '',
                        'product_unit' => $item['product_unit'] ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 1,
                        'subtotal' => $item['subtotal'] ?? 0,
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
        $orderId = request()->param('order_id', 0, 'intval');
        if ($orderId == 0) {
            return Result::validateError('订单ID不能为空');
        }

        $order = OrderModel::find($orderId);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        $file = request()->file('file');
        if (!$file) {
            return Result::validateError('请选择要上传的文件');
        }

        $validate = validate(['file' => 'fileSize:52428800|fileExt:pdf,doc,docx,jpg,jpeg,png']);
        if (!$validate->check(['file' => $file])) {
            return Result::validateError($validate->getError());
        }

        try {
            $saveName = Filesystem::disk('public')->putFile('invoices', $file);
            
            $fileInfo = $file->getInfo();
            
            $invoice = OrderInvoiceModel::create([
                'order_id' => $orderId,
                'file_name' => $fileInfo['name'],
                'file_path' => '/uploads/' . $saveName,
                'file_size' => $fileInfo['size'],
                'file_type' => $fileInfo['type'],
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '订单管理',
                '上传发票',
                '订单 ' . $order->order_no . ' 上传发票：' . $fileInfo['name']
            );

            return Result::success(['invoice_id' => $invoice->invoice_id], '上传成功');
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
