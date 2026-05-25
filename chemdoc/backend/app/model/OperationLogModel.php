<?php
declare(strict_types=1);

namespace app\model;

/**
 * 操作日志模型
 */
class OperationLogModel extends BaseModel
{
    protected $table = 'operation_log';
    protected $primaryKey = 'log_id';

    public function user()
    {
        return $this->belongsTo(AdminUserModel::class, 'user_id', 'user_id');
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('username', "%{$keyword}%")
                    ->whereOr('description', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeModule($query, string $module)
    {
        if (!empty($module)) {
            $query->where('module', $module);
        }
    }

    public function scopeAction($query, string $action)
    {
        if (!empty($action)) {
            $query->where('action', $action);
        }
    }

    public function scopeDateRange($query, array $dateRange)
    {
        if (!empty($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('create_time', $dateRange[0], $dateRange[1]);
        }
    }

    public function scopeUserId($query, int $userId)
    {
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }
    }

    public static function log(int $userId, string $username, string $module, string $action, string $description = '', array $params = []): bool
    {
        try {
            \think\facade\Db::name('operation_log')->insert([
                'user_id' => $userId,
                'username' => $username,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'request_method' => '',
                'request_url' => '',
                'request_params' => !empty($params) ? json_encode($params, JSON_UNESCAPED_UNICODE) : null,
                'client_ip' => '',
                'user_agent' => '',
                'create_time' => date('Y-m-d H:i:s'),
            ]);
            return true;
        } catch (\Exception $e) {
            error_log('OperationLogModel::log failed: ' . $e->getMessage());
            return false;
        }
    }
}
