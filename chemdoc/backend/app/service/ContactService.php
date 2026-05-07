<?php
namespace app\service;

use app\model\ContactModel;
use app\model\OperationLogModel;

class ContactService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $customerId = $params['customer_id'] ?? '';

        $query = ContactModel::with(['customer' => function ($q) {
            $q->field('customer_id,name');
        }]);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($customerId)) {
            $query->scope('customerId', (int)$customerId);
        }

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
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getDetail(int $id): array
    {
        $contact = ContactModel::with(['customer' => function ($q) {
            $q->field('customer_id,name');
        }])->find($id);

        if (!$contact) {
            return Result::notFound('联系人不存在');
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
        if (!empty($data['is_default'])) {
            ContactModel::where('customer_id', $data['customer_id'])->update(['is_default' => 0]);
        }

        $contact = ContactModel::create([
            'customer_id' => $data['customer_id'],
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'position' => $data['position'] ?? '',
            'mobile' => $data['mobile'],
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'wechat' => $data['wechat'] ?? '',
            'birthday' => $data['birthday'] ?? null,
            'remark' => $data['remark'] ?? '',
            'is_default' => $data['is_default'] ?? 0,
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

        if (!empty($data['is_default']) && empty($contact->is_default)) {
            ContactModel::where('customer_id', $contact->customer_id)->update(['is_default' => 0]);
        }

        $updateData = [];

        $fields = ['customer_id', 'name', 'gender', 'position', 'mobile', 'phone', 'email', 'wechat', 'birthday', 'remark', 'is_default'];

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
