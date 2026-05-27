<?php
declare(strict_types=1);

namespace app\model;

use app\model\AdminUserModel;
use app\model\CustomerModel;
use think\model\Relation\BelongsTo;

/**
 * 客户跟进记录模型
 */
class CustomerFollowModel extends BaseModel
{
    protected $table = 'customer_follow';
    protected $primaryKey = 'follow_id';
    protected $autoWriteTimestamp = false;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function followUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'follow_user_id', 'user_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function getMethodTextAttr($value): string
    {
        $method = ['电话' => '电话', '拜访' => '拜访', '邮件' => '邮件', '其他' => '其他'];
        return $method[$value] ?? $value;
    }

    public function getResultTextAttr($value, $data): string
    {
        $result = [1 => '有意向', 2 => '考虑中', 3 => '无意向'];
        return $result[$data['result']] ?? '未知';
    }

    public function scopeCustomerId($query, int $customerId)
    {
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
    }

    public function scopeFollowUser($query, int $userId)
    {
        if ($userId > 0) {
            $query->where('follow_user_id', $userId);
        }
    }

    public function scopeMethod($query, string $method)
    {
        if (!empty($method)) {
            $query->where('method', $method);
        }
    }

    public function scopeDateRange($query, array $dateRange)
    {
        if (!empty($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('follow_time', $dateRange[0], $dateRange[1]);
        }
    }
}
