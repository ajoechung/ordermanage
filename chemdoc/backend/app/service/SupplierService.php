<?php
namespace app\service;

use app\model\SupplierModel;
use app\model\OperationLogModel;
use app\service\DataScopeService;
use app\service\Result;
use think\facade\Db;

class SupplierService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $type = $params['type'] ?? '';
        $status = $params['status'] ?? '';
        $rating = $params['rating'] ?? '';

        $query = SupplierModel::with(['ownerUser', 'createUser']);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('code', 'like', "%{$keyword}%")
                    ->whereOr('main_products', 'like', "%{$keyword}%");
            });
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (!empty($rating)) {
            $query->where('rating', (int)$rating);
        }
        
        // 应用数据范围
        DataScopeService::applySupplierScope($query);

        $total = $query->count();
        $list = $query->order('supplier_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['attachment']) && is_string($item['attachment'])) {
                $item['attachment'] = json_decode($item['attachment'], true) ?? [];
            }
            if (isset($item['owner_user'])) {
                $item['owner_user_name'] = $item['owner_user']['realname'] ?? '';
                unset($item['owner_user']);
            }
            if (isset($item['create_user'])) {
                $item['create_user_name'] = $item['create_user']['realname'] ?? '';
                unset($item['create_user']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        // 检查权限
        if (!DataScopeService::canAccessSupplier($id)) {
            return Result::error('无权访问此供应商');
        }
        
        $supplier = SupplierModel::find($id);

        if (!$supplier) {
            return Result::notFound('供应商不存在');
        }

        $data = $supplier->toArray();

        if (isset($data['attachment']) && is_string($data['attachment'])) {
            $data['attachment'] = json_decode($data['attachment'], true) ?? [];
        }

        $data['statistics'] = [
            'order_count' => Db::name('purchase_order')->where('supplier_id', $id)->whereNull('delete_time')->count(),
            'order_amount' => Db::name('purchase_order')->where('supplier_id', $id)->whereNull('delete_time')->sum('actual_amount'),
        ];

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $exists = SupplierModel::where('name', $data['name'])->find();
        if ($exists) {
            return Result::error('供应商名称已存在');
        }

        $currentUserId = request()->user_id ?? 0;

        $supplier = new SupplierModel();
        $supplier->name = $data['name'];
        $supplier->code = $data['code'] ?? '';
        $supplier->type = $data['type'] ?? '';
        $supplier->contact = $data['contact'] ?? '';
        $supplier->phone = $data['phone'] ?? '';
        $supplier->cooperation_status = $data['cooperation_status'] ?? 'pending';
        $supplier->main_products = $data['main_products'] ?? '';
        $supplier->address = $data['address'] ?? '';
        $supplier->cooperation_start = $data['cooperation_start'] ?? null;
        $supplier->rating = $data['rating'] ?? null;
        $supplier->cert_expire_date = $data['cert_expire_date'] ?? null;
        $supplier->description = $data['description'] ?? '';
        $supplier->remark = $data['remark'] ?? '';
        $supplier->attachment = isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null;
        $supplier->status = $data['status'] ?? 1;
        $supplier->owner_user_id = $currentUserId;
        $supplier->owner_user_name = request()->username ?? '';
        $supplier->create_user_id = $currentUserId;
        $supplier->create_user_name = request()->username ?? '';
        $supplier->create_time = date('Y-m-d H:i:s');
        $supplier->save();

        OperationLogModel::log(
            $currentUserId,
            request()->username ?? '',
            '供应商管理',
            '新增',
            '新增供应商：' . $data['name']
        );

        return Result::success(['supplier_id' => $supplier->supplier_id], '供应商创建成功');
    }

    public function update(int $id, array $data): array
    {
        // 检查权限
        if (!DataScopeService::canAccessSupplier($id)) {
            return Result::error('无权访问此供应商');
        }
        
        $supplier = SupplierModel::find($id);
        if (!$supplier) {
            return Result::notFound('供应商不存在');
        }

        if (isset($data['name']) && $data['name'] != $supplier->name) {
            $exists = SupplierModel::where('name', $data['name'])->where('supplier_id', '<>', $id)->find();
            if ($exists) {
                return Result::error('供应商名称已存在');
            }
        }

        $updateData = [];

        $fields = ['name', 'code', 'type', 'contact', 'phone', 'cooperation_status', 'main_products', 'address', 'cooperation_start', 'rating', 'cert_expire_date', 'description', 'remark', 'status', 'owner_user_id'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['attachment'])) {
            $updateData['attachment'] = is_array($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : $data['attachment'];
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $supplier->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '供应商管理',
            '编辑',
            '编辑供应商：' . $supplier->name
        );

        return Result::success(null, '供应商更新成功');
    }

    public function delete(int $id): array
    {
        // 检查权限
        if (!DataScopeService::canAccessSupplier($id)) {
            return Result::error('无权访问此供应商');
        }
        
        $supplier = SupplierModel::find($id);
        if (!$supplier) {
            return Result::notFound('供应商不存在');
        }

        $hasOrders = Db::name('purchase_order')->where('supplier_id', $id)->whereNull('delete_time')->count();
        if ($hasOrders > 0) {
            return Result::error('该供应商存在关联采购单，无法删除');
        }

        $supplier->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '供应商管理',
            '删除',
            '删除供应商：' . $supplier->name
        );

        return Result::success(null, '供应商删除成功');
    }
}
