<?php
namespace app\service;

use app\model\OrderModel;
use app\model\OrderItemModel;
use app\model\ProductModel;
use app\model\CustomerModel;
use app\model\OperationLogModel;
use think\facade\Db;

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
            }
        }

        $data['total_amount'] = round((float)$data['total_amount'], 2);
        $data['actual_amount'] = round((float)$data['actual_amount'], 2);

        return Result::success($data);
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
                        'spec' => $item['spec'] ?? '',
                        'unit' => $item['unit'] ?? '',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['price'] ?? 0,
                        'amount' => $item['amount'] ?? 0,
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

        $order->order_status = $status;
        $order->save();

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
}
