<?php
namespace app\service;

use app\model\CustomerFollowModel;
use app\model\OperationLogModel;
use app\service\DataScopeService;
use app\service\Result;

class FollowService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $customerId = $params['customer_id'] ?? '';
        $userId = $params['user_id'] ?? '';
        $method = $params['method'] ?? '';
        $dateRange = $params['date_range'] ?? [];

        $query = CustomerFollowModel::with(['customer', 'followUser']);

        if (!empty($customerId)) {
            $query->scope('customerId', (int)$customerId);
        }

        if (!empty($userId)) {
            $query->scope('followUser', (int)$userId);
        }

        if (!empty($method)) {
            $query->scope('method', $method);
        }

        if (!empty($dateRange) && is_array($dateRange)) {
            $query->scope('dateRange', $dateRange);
        }
        
        DataScopeService::applyCustomerScope($query, 'customer_id');

        $total = $query->count();
        $list = $query->order('follow_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['customer'])) {
                $item['customer_name'] = $item['customer']['name'] ?? '';
                unset($item['customer']);
            }
            if (isset($item['follow_user'])) {
                $item['follow_user_name'] = $item['follow_user']['realname'] ?? $item['follow_user']['username'] ?? '';
                unset($item['follow_user']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function create(array $data): array
    {
        if (!DataScopeService::canAccessCustomer($data['customer_id'])) {
            return Result::error('无权访问此客户');
        }

        $follow = CustomerFollowModel::create([
            'customer_id' => $data['customer_id'],
            'follow_user_id' => request()->user_id ?? 0,
            'follow_time' => $data['follow_time'] ?? date('Y-m-d H:i:s'),
            'method' => $data['method'] ?? '',
            'content' => $data['content'] ?? '',
            'next_plan' => $data['next_plan'] ?? '',
            'next_time' => $data['next_time'] ?? null,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户管理',
            '新增跟进',
            '新增客户跟进记录'
        );

        return Result::success(['follow_id' => $follow->follow_id], '跟进记录创建成功');
    }

    public function delete(int $id): array
    {
        $follow = CustomerFollowModel::find($id);
        if (!$follow) {
            return Result::notFound('跟进记录不存在');
        }

        if (!DataScopeService::canAccessCustomer($follow->customer_id)) {
            return Result::error('无权删除此跟进记录');
        }

        $follow->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户管理',
            '删除跟进',
            '删除客户跟进记录'
        );

        return Result::success(null, '跟进记录删除成功');
    }
}
