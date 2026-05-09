<?php
namespace app\controller\api;

use app\BaseController;
use app\model\OperationLogModel;
use app\service\Result;

class Log extends BaseController
{
    public function index()
    {
        $params = $this->request->param();
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $module = $params['module'] ?? '';
        $action = $params['action'] ?? '';
        $dateRange = $params['date_range'] ?? [];

        $query = OperationLogModel::with('user');

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($module)) {
            $query->scope('module', $module);
        }

        if (!empty($action)) {
            $query->scope('action', $action);
        }

        if (!empty($dateRange) && is_array($dateRange)) {
            $query->scope('dateRange', $dateRange);
        }

        $total = $query->count();
        $list = $query->order('log_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        return json(Result::paginate($total, $list, $page, $pageSize));
    }

    public function batchDelete()
    {
        $data = $this->request->post();
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return json(Result::validateError('请选择要删除的日志'));
        }

        OperationLogModel::whereIn('log_id', $ids)->delete();

        return json(Result::success(null, '删除成功'));
    }

    public function clear()
    {
        OperationLogModel::where('log_id', '>', 0)->delete();

        return json(Result::success(null, '清空成功'));
    }
}
