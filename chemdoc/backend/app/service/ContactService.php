<?php
namespace app\service;

use app\model\ContactModel;
use app\model\OperationLogModel;
use app\service\DataScopeService;
use app\service\Result;
use think\facade\Db;

class ContactService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $customerId = $params['customer_id'] ?? '';

        $query = ContactModel::with(['customer', 'createUser']);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($customerId)) {
            $query->scope('customerId', (int)$customerId);
        }
        
        DataScopeService::applyCustomerScope($query, 'customer_id');

        $total = $query->count();
        $list = $query->order('contact_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['customer'])) {
                $item['customer_name'] = $item['customer']['name'] ?? '';
                unset($item['customer']);
            }
            if (isset($item['create_user'])) {
                $item['create_user_name'] = $item['create_user']['realname'] ?? $item['create_user']['username'] ?? '';
                unset($item['create_user']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $contact = ContactModel::with(['customer'])->find($id);

        if (!$contact) {
            return Result::notFound('联系人不存在');
        }

        if (!DataScopeService::canAccessCustomer($contact->customer_id)) {
            return Result::error('无权访问此联系人');
        }

        $data = $contact->toArray();

        if (isset($data['customer'])) {
            $data['customer_name'] = $data['customer']['name'] ?? '';
            unset($data['customer']);
        }

        return Result::success($data);
    }

    public function create(array $data): array
    {
        if (!DataScopeService::canAccessCustomer($data['customer_id'])) {
            return Result::error('无权访问此客户');
        }

        $contact = ContactModel::create([
            'customer_id' => $data['customer_id'],
            'name' => $data['name'],
            'position' => $data['position'] ?? '',
            'phone' => $data['phone'] ?? '',
            'mobile' => $data['mobile'] ?? '',
            'email' => $data['email'] ?? '',
            'wechat' => $data['wechat'] ?? '',
            'qq' => $data['qq'] ?? '',
            'gender' => $data['gender'] ?? 1,
            'is_default' => $data['is_default'] ?? 0,
            'remark' => $data['remark'] ?? '',
            'create_user_id' => request()->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '联系人管理',
            '新增',
            '新增联系人：' . $data['name']
        );

        return Result::success(['contact_id' => $contact->contact_id], '联系人创建成功');
    }

    public function update(int $id, array $data): array
    {
        $contact = ContactModel::find($id);
        if (!$contact) {
            return Result::notFound('联系人不存在');
        }

        if (!DataScopeService::canAccessCustomer($contact->customer_id)) {
            return Result::error('无权访问此联系人');
        }

        if (isset($data['customer_id']) && $data['customer_id'] != $contact->customer_id) {
            if (!DataScopeService::canAccessCustomer($data['customer_id'])) {
                return Result::error('无权访问目标客户');
            }
        }

        $updateData = [];

        $fields = ['customer_id', 'name', 'position', 'phone', 'mobile', 'email', 'wechat', 'qq', 'gender', 'is_default', 'remark'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $contact->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '联系人管理',
            '编辑',
            '编辑联系人：' . $contact->name
        );

        return Result::success(null, '联系人更新成功');
    }

    public function delete(int $id): array
    {
        $contact = ContactModel::find($id);
        if (!$contact) {
            return Result::notFound('联系人不存在');
        }

        if (!DataScopeService::canAccessCustomer($contact->customer_id)) {
            return Result::error('无权访问此联系人');
        }

        $hasOrders = Db::name('order')->where('contact_id', $id)->whereNull('delete_time')->count();
        if ($hasOrders > 0) {
            return Result::error('该联系人存在关联订单，无法删除');
        }

        $contact->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '联系人管理',
            '删除',
            '删除联系人：' . $contact->name
        );

        return Result::success(null, '联系人删除成功');
    }
}
