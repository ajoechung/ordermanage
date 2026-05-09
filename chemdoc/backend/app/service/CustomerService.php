<?php
namespace app\service;

use app\model\CustomerModel;
use app\model\OperationLogModel;
use think\facade\Db;

class CustomerService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $industry = $params['industry'] ?? '';
        $level = $params['level'] ?? '';
        $ownerUserId = $params['owner_user_id'] ?? '';
        $dateRange = $params['date_range'] ?? [];

        $query = CustomerModel::with(['ownerUser']);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($industry)) {
            $query->scope('industry', $industry);
        }

        if (!empty($level)) {
            $query->scope('level', (int)$level);
        }

        if (!empty($ownerUserId)) {
            $query->scope('ownerUser', (int)$ownerUserId);
        }

        if (!empty($dateRange) && is_array($dateRange)) {
            $query->scope('dateRange', $dateRange);
        }

        $total = $query->count();
        $list = $query->order('customer_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['owner_user'])) {
                $item['owner_name'] = $item['owner_user']['realname'] ?? '';
                unset($item['owner_user']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $customer = CustomerModel::with(['ownerUser'])->find($id);

        if (!$customer) {
            return Result::notFound('客户不存在');
        }

        $data = $customer->toArray();

        if (isset($data['owner_user'])) {
            $data['owner_name'] = $data['owner_user']['realname'] ?? '';
            unset($data['owner_user']);
        }

        if (isset($data['attachment']) && is_string($data['attachment'])) {
            $data['attachment'] = json_decode($data['attachment'], true) ?? [];
        }

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $exists = CustomerModel::where('name', $data['name'])->find();
        if ($exists) {
            return Result::error('客户名称已存在');
        }

        $customer = CustomerModel::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? '',
            'industry' => $data['industry'] ?? '',
            'source' => $data['source'] ?? '',
            'scale' => $data['scale'] ?? '',
            'address' => $data['address'] ?? '',
            'annual_revenue' => $data['annual_revenue'] ?? 0,
            'description' => $data['description'] ?? '',
            'attachment' => isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null,
            'status' => $data['status'] ?? 1,
            'level' => $data['level'] ?? 1,
            'owner_user_id' => $data['owner_user_id'] ?? 0,
            'create_user_id' => request()->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户管理',
            '新增',
            '新增客户：' . $data['name']
        );

        return Result::success(['customer_id' => $customer->customer_id], '客户创建成功');
    }

    public function update(int $id, array $data): array
    {
        $customer = CustomerModel::find($id);
        if (!$customer) {
            return Result::notFound('客户不存在');
        }

        if (isset($data['name']) && $data['name'] != $customer->name) {
            $exists = CustomerModel::where('name', $data['name'])->where('customer_id', '<>', $id)->find();
            if ($exists) {
                return Result::error('客户名称已存在');
            }
        }

        $updateData = [];

        $fields = ['name', 'code', 'industry', 'source', 'scale', 'address', 'annual_revenue', 'description', 'status', 'level', 'owner_user_id'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['attachment'])) {
            $updateData['attachment'] = is_array($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : $data['attachment'];
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $customer->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户管理',
            '编辑',
            '编辑客户：' . $customer->name
        );

        return Result::success(null, '客户更新成功');
    }

    public function delete(int $id): array
    {
        $customer = CustomerModel::find($id);
        if (!$customer) {
            return Result::notFound('客户不存在');
        }

        $hasContacts = Db::name('contact')->where('customer_id', $id)->whereNull('delete_time')->count();
        if ($hasContacts > 0) {
            return Result::error('该客户存在联系人，无法删除');
        }

        $hasOrders = Db::name('order')->where('customer_id', $id)->whereNull('delete_time')->count();
        if ($hasOrders > 0) {
            return Result::error('该客户存在订单，无法删除');
        }

        $customer->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户管理',
            '删除',
            '删除客户：' . $customer->name
        );

        return Result::success(null, '客户删除成功');
    }
}
