<?php
namespace app\service;

use app\model\PurchaseOrderModel;
use app\model\PurchaseItemModel;
use app\model\OrderModel;
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
        $dateRange = $params['date_range'] ?? [];

        $query = PurchaseOrderModel::with(['supplier', 'createUser', 'order']);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('order_no', "%{$keyword}%");
            });
        }

        if (!empty($supplierId)) {
            $query->where('supplier_id', (int)$supplierId);
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        if (!empty($dateRange) && is_array($dateRange) && count($dateRange) == 2) {
            $query->whereTime('order_time', 'between', $dateRange);
        }
        
        // 应用数据范围
        DataScopeService::applySupplierScope($query);

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
            if (isset($item['create_user'])) {
                $item['create_name'] = $item['create_user']['realname'] ?? '';
                unset($item['create_user']);
            }
            if (isset($item['order'])) {
                $item['order_no'] = $item['order']['order_no'] ?? '';
                unset($item['order']);
            }
            $item['total_amount'] = round((float)$item['total_amount'], 2);
            $item['paid_amount'] = round((float)$item['paid_amount'], 2);
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $order = PurchaseOrderModel::with(['supplier', 'createUser', 'items.product'])->find($id);

        if (!$order) {
            return Result::notFound('采购单不存在');
        }
        
        // 检查权限
        if (!DataScopeService::canAccessSupplier($order->supplier_id)) {
            return Result::error('无权访问此采购单');
        }

        $data = $order->toArray();

        if (isset($data['supplier'])) {
            $data['supplier_name'] = $data['supplier']['name'] ?? '';
            unset($data['supplier']);
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
        $data['paid_amount'] = round((float)$data['paid_amount'], 2);

        return Result::success($data);
    }

    public function create(array $data): array
    {
        Db::startTrans();
        try {
            $totalAmount = 0;
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $totalAmount += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                }
            }

            $order = PurchaseOrderModel::create([
                'order_no' => $this->generateOrderNo(),
                'supplier_id' => $data['supplier_id'],
                'order_id' => $data['order_id'] ?? null,
                'contact' => $data['contact'] ?? '',
                'phone' => $data['phone'] ?? '',
                'total_amount' => $totalAmount,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'order_time' => $data['order_time'] ?? date('Y-m-d H:i:s'),
                'expect_arrival_date' => $data['expected_date'] ?? $data['expect_arrival_date'] ?? null,
                'actual_arrival_date' => $data['actual_arrival_date'] ?? null,
                'remark' => $data['remark'] ?? '',
                'status' => 1,
                'create_user_id' => request()->user_id ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
            ]);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    PurchaseItemModel::create([
                        'purchase_id' => $order->purchase_id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'spec' => $item['product_spec'] ?? $item['spec'] ?? '',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['unit_price'] ?? $item['price'] ?? 0,
                        'amount' => ($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 0),
                    ]);
                }
            }

            Db::commit();

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '采购单管理',
                '新增',
                '新增采购单：' . $order->order_no
            );

            return Result::success(['purchase_id' => $order->purchase_id], '采购单创建成功');
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
        
        // 检查权限
        if (!DataScopeService::canAccessSupplier($order->supplier_id)) {
            return Result::error('无权访问此采购单');
        }

        if ($order->status > 1) {
            return Result::error('已确认的采购单无法修改');
        }

        Db::startTrans();
        try {
            $totalAmount = $order->total_amount;
            if (isset($data['items']) && is_array($data['items'])) {
                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $totalAmount += ($item['price'] ?? $item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
                }
            }

            $updateData = [
                'supplier_id' => $data['supplier_id'] ?? $order->supplier_id,
                'order_id' => $data['order_id'] ?? $order->order_id,
                'contact' => $data['contact'] ?? $order->contact,
                'phone' => $data['phone'] ?? $order->phone,
                'total_amount' => $totalAmount,
                'paid_amount' => $data['paid_amount'] ?? 0,
                'expect_arrival_date' => $data['expected_date'] ?? $data['expect_arrival_date'] ?? $order->expect_arrival_date,
                'remark' => $data['remark'] ?? '',
                'update_time' => date('Y-m-d H:i:s'),
            ];

            $order->save($updateData);

            if (isset($data['items']) && is_array($data['items'])) {
                // 删除旧的items
                PurchaseItemModel::where('purchase_id', $id)->delete();
                
                // 添加新的items
                foreach ($data['items'] as $item) {
                    PurchaseItemModel::create([
                        'purchase_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'] ?? '',
                        'spec' => $item['product_spec'] ?? $item['spec'] ?? '',
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => $item['unit_price'] ?? $item['price'] ?? 0,
                        'amount' => ($item['unit_price'] ?? $item['price'] ?? 0) * ($item['quantity'] ?? 0),
                    ]);
                }
            }

            Db::commit();

            OperationLogModel::log(
                request()->user_id ?? 0,
                request()->username ?? '',
                '采购单管理',
                '编辑',
                '编辑采购单：' . $order->order_no
            );

            return Result::success(null, '采购单更新成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Result::error('采购单更新失败：' . $e->getMessage());
        }
    }

    public function delete(int $id): array
    {
        $order = PurchaseOrderModel::find($id);
        if (!$order) {
            return Result::notFound('采购单不存在');
        }
        
        // 检查权限
        if (!DataScopeService::canAccessSupplier($order->supplier_id)) {
            return Result::error('无权访问此采购单');
        }

        if ($order->status > 1) {
            return Result::error('已确认的采购单无法删除');
        }

        PurchaseItemModel::where('purchase_id', $id)->delete();
        $order->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '采购单管理',
            '删除',
            '删除采购单：' . $order->order_no
        );

        return Result::success(null, '采购单删除成功');
    }

    public function updateStatus(int $id, int $status): array
    {
        $order = PurchaseOrderModel::find($id);
        if (!$order) {
            return Result::notFound('采购单不存在');
        }
        
        // 检查权限
        if (!DataScopeService::canAccessSupplier($order->supplier_id)) {
            return Result::error('无权访问此采购单');
        }

        $updateData = ['status' => $status];
        
        if ($status == 3 && empty($order->actual_arrival_date)) {
            $updateData['actual_arrival_date'] = date('Y-m-d H:i:s');
        }

        $order->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '采购单管理',
            '状态变更',
            '采购单 ' . $order->order_no . ' 状态变更为：' . $this->getStatusText($status)
        );

        return Result::success(null, '状态更新成功');
    }

    protected function generateOrderNo(): string
    {
        return 'PO' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
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
}
