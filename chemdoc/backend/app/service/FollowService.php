<?php
namespace app\service;

use app\model\CustomerFollowModel;
use app\model\OperationLogModel;

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

        $query = CustomerFollowModel::with([
            'customer' => function ($q) {
                $q->field('customer_id,name');
            },
            'followUser' => function ($q) {
                $q->field('user_id,realname');
            }
        ]);

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
                $item['follow_user_name'] = $item['follow_user']['realname'] ?? '';
                unset($item['follow_user']);
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function create(array $data): array
    {
        $follow = CustomerFollowModel::create([
            'customer_id' => $data['customer_id'],
            'follow_user_id' => $data['follow_user_id'] ?? (request()->user_id ?? 0),
            'method' => $data['method'],
            'content' => $data['content'],
            'follow_time' => $data['follow_time'] ?? date('Y-m-d H:i:s'),
            'next_follow_time' => $data['next_follow_time'] ?? null,
            'result' => $data['result'] ?? null,
            'create_user_id' => request()->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户跟进',
            '新增',
            '新增跟进记录'
        );

        return Result::success(['follow_id' => $follow->follow_id], '跟进记录创建成功');
    }

    public function delete(int $id): array
    {
        $follow = CustomerFollowModel::find($id);
        if (!$follow) {
            return Result::notFound('跟进记录不存在');
        }

        $follow->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '客户跟进',
            '删除',
            '删除跟进记录'
        );

        return Result::success(null, '跟进记录删除成功');
    }
}
