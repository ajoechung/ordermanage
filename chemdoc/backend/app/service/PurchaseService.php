<?php
namespace app\service;

use app\model\PurchaseOrderModel;
use app\model\PurchaseItemModel;
use app\model\SupplierModel;
use app\model\ProductModel;
use app\model\OperationLogModel;
use think\facade\Db;

class PurchaseService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $supplierId = $params['supplier_id'] ?? '';
        $status = $params['status'] ?? '';

        $query = PurchaseOrderModel::with(['supplier' => function ($q) {
            $q->field('supplier_id,name');
        }]);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('order_no', "%{$keyword}%");
            });
        }

        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->order('purchase_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['supplier'])) {
                $item['supplier_name'] = $item['supplier']['name'] ?? '';
                unset($item['supplier']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $order = PurchaseOrderModel::with([
            'supplier' => function ($q) {
                $q->field('supplier_id,name,contact,phone');
            },
            'items.product'
        ])->find($id);

        if (!$order) {
            return Result::notFound('采购单不存在');
        }

        $data = $order->toArray();

        if (isset($data['supplier'])) {
            $data['supplier_name'] = $data['supplier']['name'] ?? '';
            $data['contact'] = $data['supplier']['contact'] ?? '';
            $data['phone'] = $data['supplier']['phone'] ?? '';
            unset($data['supplier']);
        }

        return Result::success($data);
    }

    public function create(array $data): array
    {
        Db::startTrans();
        try {
            $orderNo = $this->generateOrderNo();

            $supplier = SupplierModel::find($data['supplier_id']);
            if (!$supplier) {
                throw new \Exception('供应商不存在');
            }

            $totalAmount = 0;
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $totalAmount += ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                }
            }

            $order = PurchaseOrderModel::create([
                'order_no' => $orderNo,
                'supplier_id' => $data['supplier_id'],
                'contact' => $data['contact'] ?? $supplier->contact,
                'phone' => $data['phone'] ?? $supplier->phone,
                'expected_date' => $data['expected_date'] ?? null,
                'total_amount' => $totalAmount,
                'status' => 1,
                'remark' => $data['remark'] ?? '',
                'create_user_id' => request()->user_id ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $product = ProductModel::find($item['product_id']);
                    if ($product) {
                        PurchaseItemModel::create([
                            'purchase_id' => $order->purchase_id,
                            'product_id' => $item['product_id'],
                            'product_name' => $product->name,
                            'product_spec' => $product->spec ?? '',
                            'product_unit' => $product->unit ?? '',
                            'unit_price' => $item['unit_price'] ?? 0,
                            'quantity' => $item['quantity'] ?? 0,
                            'subtotal' => ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0),
                            'create_time' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            Db::commit();

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '采购管理',
                '新增',
                '新增采购单：' . $orderNo
            );

            return Result::success(['purchase_id' => $order->purchase_id, 'order_no' => $orderNo], '采购单创建成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Result::error('采购单创建失败：' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): array
    {
        $order = PurchaseOrderModel::find($id);
        if (!$order) {
            return Result::notFound('采购单不存在');
        }

        if ($order->status > 1) {
            return Result::error('已确认的采购单无法编辑');
        }

        $updateData = [];

        $fields = ['supplier_id', 'contact', 'phone', 'expected_date', 'remark'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['items'])) {
            PurchaseItemModel::where('purchase_id', $id)->delete();
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $product = ProductModel::find($item['product_id']);
                if ($product) {
                    $subtotal = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                    $totalAmount += $subtotal;
                    PurchaseItemModel::create([
                        'purchase_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name,
                        'product_spec' => $product->spec ?? '',
                        'product_unit' => $product->unit ?? '',
                        'unit_price' => $item['unit_price'] ?? 0,
                        'quantity' => $item['quantity'] ?? 0,
                        'subtotal' => $subtotal,
                        'create_time' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            $updateData['total_amount'] = $totalAmount;
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');
        $order->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '采购管理',
            '编辑',
            '编辑采购单：' . $order->order_no
        );

        return Result::success(null, '采购单更新成功');
    }

    public function updateStatus(int $id, int $status): array
    {
        $order = PurchaseOrderModel::find($id);
        if (!$order) {
            return Result::notFound('采购单不存在');
        }

        $allowedTransitions = [
            1 => [2, 6],
            2 => [3, 6],
            3 => [4, 6],
            4 => [5],
            5 => [],
            6 => [],
        ];

        $allowed = $allowedTransitions[$order->status] ?? [];
        if (!in_array($status, $allowed)) {
            return Result::error('不允许的状态转换');
        }

        $updateData = ['status' => $status, 'update_time' => date('Y-m-d H:i:s')];

        if ($status == 5) {
            $updateData['receive_date'] = date('Y-m-d H:i:s');
        }

        $order->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '采购管理',
            '状态更新',
            '采购单状态变更为：' . $this->getStatusText($status)
        );

        return Result::success(null, '状态更新成功');
    }

    protected function getStatusText(int $status): string
    {
        $statusMap = [
            1 => '草稿',
            2 => '已提交',
            3 => '已确认',
            4 => '已入库',
            5 => '已完成',
            6 => '已取消',
        ];
        return $statusMap[$status] ?? '未知';
    }

    protected function generateOrderNo(): string
    {
        $date = date('Ymd');
        $count = PurchaseOrderModel::whereDay('create_time', 'today')->count() + 1;
        return 'PO' . $date . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
    }

    public function delete(int $id): array
    {
        $order = PurchaseOrderModel::find($id);
        if (!$order) {
            return Result::notFound('采购单不存在');
        }

        if ($order->status > 1) {
            return Result::error('已确认的采购单无法删除');
        }

        PurchaseItemModel::where('purchase_id', $id)->delete();
        $order->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '采购管理',
            '删除',
            '删除采购单：' . $order->order_no
        );

        return Result::success(null, '采购单删除成功');
    }
}
