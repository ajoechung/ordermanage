<?php
namespace app\service;

use app\model\CustomerModel;
use app\model\OperationLogModel;
use app\model\ContactModel;
use app\model\CustomerFollowModel;
use app\model\OrderModel;
use app\model\OrderItemModel;
use app\model\ProductModel;
use app\service\DataScopeService;
use app\service\Result;
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

        // 调试日志
        $userId = request()->user_id ?? 0;
        $username = request()->username ?? '';
        error_log("CustomerService::getList - userId: {$userId}, username: {$username}");

        $query = CustomerModel::with(['ownerUser', 'createUser']);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('code', 'like', "%{$keyword}%");
            });
        }

        if (!empty($industry)) {
            $query->where('industry', $industry);
        }

        if (!empty($level)) {
            $query->where('level', (int)$level);
        }

        if (!empty($ownerUserId)) {
            $query->where('owner_user_id', (int)$ownerUserId);
        }

        if (!empty($dateRange) && is_array($dateRange) && count($dateRange) === 2) {
            $query->whereBetween('create_time', $dateRange);
        }
        
        DataScopeService::applyCustomerScope($query);

        $total = $query->count();
        $list = $query->order('customer_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['owner_user'])) {
                $item['owner_user_name'] = $item['owner_user']['realname'] ?? $item['owner_user']['username'] ?? '';
                unset($item['owner_user']);
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
        // 检查权限
        if (!DataScopeService::canAccessCustomer($id)) {
            return Result::error('无权访问此客户');
        }

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

    public function getFullDetail(int $id): array
    {
        // 检查权限
        if (!DataScopeService::canAccessCustomer($id)) {
            return Result::error('无权访问此客户');
        }

        $customer = CustomerModel::with(['ownerUser', 'createUser'])->find($id);

        if (!$customer) {
            return Result::notFound('客户不存在');
        }

        $data = $customer->toArray();

        if (isset($data['owner_user'])) {
            $data['owner_name'] = $data['owner_user']['realname'] ?? $data['owner_user']['username'] ?? '';
            unset($data['owner_user']);
        }
        if (isset($data['create_user'])) {
            $data['create_user_name'] = $data['create_user']['realname'] ?? $data['create_user']['username'] ?? '';
            unset($data['create_user']);
        }

        // 获取联系人列表
        $contacts = ContactModel::where('customer_id', $id)
            ->order('is_default desc, contact_id asc')
            ->select()
            ->toArray();
        foreach ($contacts as &$contact) {
            $contact['is_primary'] = $contact['is_default'] ?? 0;
        }
        $data['contacts'] = $contacts;

        // 获取跟进记录
        $follows = CustomerFollowModel::where('customer_id', $id)
            ->with(['createUser'])
            ->order('create_time desc')
            ->select()
            ->toArray();
        foreach ($follows as &$follow) {
            $follow['create_user_name'] = $follow['create_user']['realname'] ?? '';
            $follow['type'] = $this->getFollowTypeCode($follow['method']);
            unset($follow['create_user']);
        }
        $data['follows'] = $follows;

        // 获取历史订单
        $orders = OrderModel::where('customer_id', $id)
            ->order('create_time desc')
            ->select()
            ->toArray();
        foreach ($orders as &$order) {
            $order['status'] = $order['order_status'] ?? 0;
            if (!isset($order['customer_name'])) {
                $order['customer_name'] = $customer->name ?? '';
            }
        }
        $data['orders'] = $orders;

        // 获取交易产品（从订单商品中统计）
        $productData = Db::name('order_item')
            ->alias('oi')
            ->join('product p', 'oi.product_id = p.product_id')
            ->join('order o', 'oi.order_id = o.order_id')
            ->where('o.customer_id', $id)
            ->where('o.order_status', 'in', [3, 4, 5])
            ->field('p.name as product_name, p.spec, p.unit, p.price, SUM(oi.quantity) as quantity, SUM(oi.subtotal) as total_amount, MAX(oi.create_time) as last_trade_time')
            ->group('oi.product_id')
            ->order('total_amount desc')
            ->select()
            ->toArray();
        $data['products'] = $productData;

        // 获取操作日志
        $logs = OperationLogModel::where('module', '客户管理')
            ->where('description', 'like', "%{$customer->name}%")
            ->order('create_time desc')
            ->select()
            ->toArray();
        $data['logs'] = $logs;

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $exists = CustomerModel::where('name', $data['name'])->find();
        if ($exists) {
            return Result::error('客户名称已存在');
        }

        $currentUserId = request()->user_id ?? 0;
        if ($currentUserId == 0) {
            return Result::error('无法获取当前用户信息');
        }

        $customer = new CustomerModel();
        $customer->name = $data['name'];
        $customer->industry = $data['industry'] ?? '';
        $customer->source = $data['source'] ?? '';
        $customer->scale = $data['scale'] ?? '';
        $customer->address = $data['address'] ?? '';
        $customer->annual_revenue = $data['annual_revenue'] ?? 0;
        $customer->description = $data['description'] ?? '';
        if (!empty(trim($data['code'] ?? ''))) {
            $customer->code = $data['code'];
        } else {
            $customer->code = null;
        }
        $customer->attachment = isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null;
        $customer->status = $data['status'] ?? 1;
        $customer->level = $data['level'] ?? 1;
        $customer->owner_user_id = $currentUserId;
        $customer->owner_user_name = request()->username ?? '';
        $customer->create_user_id = $currentUserId;
        $customer->create_user_name = request()->username ?? '';
        $customer->create_time = date('Y-m-d H:i:s');
        $customer->save();

        OperationLogModel::log(
            $currentUserId,
            request()->username ?? '',
            '客户管理',
            '新增',
            '新增客户：' . $data['name']
        );

        return Result::success(['customer_id' => $customer->customer_id], '客户创建成功');
    }

    public function update(int $id, array $data): array
    {
        try {
            if (!DataScopeService::canAccessCustomer($id)) {
                return Result::error('无权访问此客户');
            }
            
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

            $fields = ['name', 'industry', 'source', 'scale', 'address', 'annual_revenue', 'description', 'status', 'level', 'owner_user_id'];

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (isset($data['code'])) {
                $updateData['code'] = !empty(trim($data['code'])) ? $data['code'] : null;
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
        } catch (\Exception $e) {
            return Result::error('更新失败：' . $e->getMessage());
        }
    }

    public function delete(int $id, bool $force = false): array
    {
        try {
            if (!DataScopeService::canAccessCustomer($id)) {
                return Result::error('无权访问此客户');
            }
            
            $customer = CustomerModel::find($id);
            if (!$customer) {
                return Result::notFound('客户不存在');
            }

            $hasOrders = Db::name('order')->where('customer_id', $id)->whereNull('delete_time')->count();
            if ($hasOrders > 0) {
                return Result::error('该客户存在订单，无法删除');
            }

            $hasContacts = Db::name('contact')->where('customer_id', $id)->count();
            if ($hasContacts > 0) {
                if (!$force) {
                    return [
                        'code' => 201,
                        'msg' => '该客户存在联系人，确认删除将同时删除所有联系人',
                        'data' => ['contact_count' => $hasContacts]
                    ];
                }
                Db::name('contact')->where('customer_id', $id)->delete();
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
        } catch (\Exception $e) {
            return Result::error('删除失败：' . $e->getMessage());
        }
    }

    private function getFollowTypeCode(string $method): int
    {
        $typeMap = [
            '电话' => 1,
            '微信' => 2,
            '邮件' => 3,
            '拜访' => 4,
            '面谈' => 4,
            '其他' => 5,
        ];
        return $typeMap[$method] ?? 5;
    }
}
