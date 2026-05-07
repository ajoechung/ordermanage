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

        $query = OrderModel::with([
            'customer' => function ($q) {
                $q->field('customer_id,name');
            },
            'createUser' => function ($q) {
                $q->field('user_id,realname');
            }
        ]);

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
        $order = OrderModel::with([
            'customer' => function ($q) {
                $q->field('customer_id,name,industry,address');
            },
            'contact' => function ($q) {
                $q->field('contact_id,customer_id,name,mobile');
            },
            'items.product',
            'createUser' => function ($q) {
                $q->field('user_id,realname');
            },
            'purchaseOrder' => function ($q) {
                $q->field('purchase_order_id,purchase_no,supplier_id');
            }
        ])->find($id);

        if (!$order) {
            return Result::notFound('订单不存在');
        }

        $data = $order->toArray();

        $data['customer_name'] = $data['customer']['name'] ?? '';
        $data['customer_industry'] = $data['customer']['industry'] ?? '';
        $data['customer_address'] = $data['customer']['address'] ?? '';
        unset($data['customer']);

        if (isset($data['contact'])) {
            $data['contact_name'] = $data['contact']['name'] ?? '';
            $data['contact_mobile'] = $data['contact']['mobile'] ?? '';
            unset($data['contact']);
        }

        if (isset($data['purchase_order'])) {
            $data['purchase_no'] = $data['purchase_order']['purchase_no'] ?? '';
            unset($data['purchase_order']);
        }

        $data['create_name'] = $data['create_user']['realname'] ?? '';
        unset($data['create_user']);

        $data['total_amount'] = round((float)$data['total_amount'], 2);
        $data['actual_amount'] = round((float)$data['actual_amount'], 2);

        return Result::success($data);
    }

    public function create(array $data): array
    {
        Db::startTrans();
        try {
            $orderNo = $this->generateOrderNo();

            $customer = CustomerModel::find($data['customer_id']);
            if (!$customer) {
                throw new \Exception('客户不存在');
            }

            $order = OrderModel::create([
                'order_no' => $orderNo,
                'customer_id' => $data['customer_id'],
                'contact_id' => $data['contact_id'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'actual_amount' => $data['actual_amount'] ?? 0,
                'order_time' => $data['order_time'] ?? date('Y-m-d H:i:s'),
                'expect_delivery_date' => $data['expect_delivery_date'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? $customer->address,
                'remark' => $data['remark'] ?? '',
                'order_status' => 1,
                'invoice_status' => 1,
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'create_user_id' => request()->user_id ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $product = ProductModel::find($item['product_id']);
                    if (!$product) {
                        throw new \Exception('产品不存在');
                    }

                    OrderItemModel::create([
                        'order_id' => $order->order_id,
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'product_spec' => $product->spec,
                        'product_unit' => $product->unit,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'shipped_quantity' => 0,
                        'subtotal' => $item['quantity'] * $item['unit_price'],
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
                '新增订单：' . $orderNo
            );

            return Result::success(['order_id' => $order->order_id, 'order_no' => $orderNo], '订单创建成功');

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

        if (in_array($order->order_status, [5, 6])) {
            return Result::error('已完成或已取消的订单无法编辑');
        }

        $updateData = [];

        $fields = ['customer_id', 'contact_id', 'discount_amount', 'actual_amount', 'expect_delivery_date', 'delivery_address', 'remark', 'purchase_order_id'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['items'])) {
            OrderItemModel::where('order_id', $id)->delete();
            foreach ($data['items'] as $item) {
                $product = ProductModel::find($item['product_id']);
                if ($product) {
                    OrderItemModel::create([
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'product_spec' => $product->spec,
                        'product_unit' => $product->unit,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'shipped_quantity' => 0,
                        'subtotal' => $item['quantity'] * $item['unit_price'],
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');
        $order->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '订单管理',
            '编辑',
            '编辑订单：' . $order->order_no
        );

        return Result::success(null, '订单更新成功');
    }

    public function updateStatus(int $id, int $status): array
    {
        $order = OrderModel::find($id);
        if (!$order) {
            return Result::notFound('订单不存在');
        }

        $allowedTransitions = [
            1 => [2, 6],
            2 => [3, 6],
            3 => [4, 6],
            4 => [5, 6],
            5 => [],
            6 => [],
        ];

        $allowed = $allowedTransitions[$order->order_status] ?? [];
        if (!in_array($status, $allowed)) {
            return Result::error('不允许的状态转换');
        }

        $updateData = ['order_status' => $status, 'update_time' => date('Y-m-d H:i:s')];

        if ($status == 4) {
            $updateData['actual_delivery_date'] = date('Y-m-d H:i:s');
        }

        $order->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '订单管理',
            '状态更新',
            '订单状态变更为：' . $this->getStatusText($status)
        );

        return Result::success(null, '状态更新成功');
    }

    protected function getStatusText(int $status): string
    {
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消',
        ];
        return $statusMap[$status] ?? '未知';
    }

    protected function generateOrderNo(): string
    {
        $date = date('Ymd');
        $count = OrderModel::whereDay('create_time', 'today')->count() + 1;
        return 'ORD' . $date . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
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
}
