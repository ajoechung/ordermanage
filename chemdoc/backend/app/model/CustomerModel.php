<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\HasMany;

/**
 * 客户模型
 */
class CustomerModel extends BaseModel
{
    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    
    // 允许批量赋值的字段
    protected $fillable = [
        'name', 'code', 'industry', 'source', 'scale', 'address', 
        'annual_revenue', 'description', 'attachment', 'status', 'level', 
        'owner_user_id', 'create_user_id', 'create_time', 'update_time'
    ];

    public function ownerUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'owner_user_id', 'user_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ContactModel::class, 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class, 'customer_id');
    }

    public function follows(): HasMany
    {
        return $this->hasMany(CustomerFollowModel::class, 'customer_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [0 => '禁用', 1 => '正常'];
        return $status[$data['status']] ?? '未知';
    }

    public function getLevelTextAttr($value, $data): string
    {
        $level = [1 => '普通客户', 2 => '重要客户', 3 => '核心客户'];
        return $level[$data['level']] ?? '未知';
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('code', 'like', "%{$keyword}%")
                    ->whereOr('address', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeIndustry($query, string $industry)
    {
        if (!empty($industry)) {
            $query->where('industry', $industry);
        }
    }

    public function scopeOwnerUser($query, int $userId)
    {
        if ($userId > 0) {
            $query->where('owner_user_id', $userId);
        }
    }

    public function scopeLevel($query, int $level)
    {
        if ($level > 0) {
            $query->where('level', $level);
        }
    }

    public function scopeDateRange($query, array $dateRange)
    {
        if (!empty($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('create_time', $dateRange[0], $dateRange[1]);
        }
    }
}
