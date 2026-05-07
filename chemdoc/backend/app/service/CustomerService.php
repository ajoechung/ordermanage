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
        $status = $params['status'] ?? '';
        $ownerUserId = $params['owner_user_id'] ?? '';
        $level = $params['level'] ?? '';
        $dateRange = $params['date_range'] ?? [];

        $query = CustomerModel::with(['ownerUser' => function ($q) {
            $q->field('user_id,realname');
        }]);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($industry)) {
            $query->scope('industry', $industry);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (!empty($ownerUserId)) {
            $query->scope('ownerUser', (int)$ownerUserId);
        }

        if (!empty($level)) {
            $query->scope('level', (int)$level);
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
            if (isset($item['attachment']) && is_string($item['attachment'])) {
                $item['attachment'] = json_decode($item['attachment'], true) ?? [];
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $customer = CustomerModel::with(['ownerUser' => function ($q) {
            $q->field('user_id,realname');
        }, 'contacts' => function ($q) {
            $q->field('contact_id,customer_id,name,mobile,position,is_default');
        }])->find($id);

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

        $data['statistics'] = [
            'order_count' => Db::name('order')->where('customer_id', $id)->count(),
            'order_amount' => Db::name('order')->where('customer_id', $id)->sum('actual_amount'),
            'follow_count' => Db::name('customer_follow')->where('customer_id', $id)->count(),
            'contact_count' => Db::name('contact')->where('customer_id', $id)->count(),
        ];

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $customer = CustomerModel::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? '',
            'industry' => $data['industry'] ?? '',
            'source' => $data['source'] ?? '',
            'owner_user_id' => $data['owner_user_id'] ?? null,
            'address' => $data['address'] ?? '',
            'scale' => $data['scale'] ?? '',
            'annual_revenue' => $data['annual_revenue'] ?? null,
            'description' => $data['description'] ?? '',
            'attachment' => isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null,
            'status' => $data['status'] ?? 1,
            'level' => $data['level'] ?? 1,
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

        $updateData = [];

        $fields = ['name', 'code', 'industry', 'source', 'owner_user_id', 'address', 'scale', 'annual_revenue', 'description', 'status', 'level'];

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

        $hasOrders = Db::name('order')->where('customer_id', $id)->whereNull('delete_time')->count();
        if ($hasOrders > 0) {
            return Result::error('该客户存在关联订单，无法删除');
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
