<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\HasMany;

/**
 * 供应商模型
 */
class SupplierModel extends BaseModel
{
    protected $table = 'supplier';
    protected $primaryKey = 'supplier_id';
    
    // 允许批量赋值的字段
    protected $fillable = [
        'name', 'code', 'type', 'main_products', 'address', 'cooperation_start',
        'rating', 'cert_expire_date', 'description', 'attachment', 'status',
        'owner_user_id', 'create_user_id', 'create_time', 'update_time'
    ];

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }
    
    public function ownerUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'owner_user_id', 'user_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrderModel::class, 'supplier_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [0 => '禁用', 1 => '正常'];
        return $status[$data['status']] ?? '未知';
    }

    public function getRatingTextAttr($value, $data): string
    {
        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('code', 'like', "%{$keyword}%")
                    ->whereOr('main_products', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeType($query, string $type)
    {
        if (!empty($type)) {
            $query->where('type', $type);
        }
    }

    public function scopeRating($query, int $rating)
    {
        if ($rating > 0) {
            $query->where('rating', $rating);
        }
    }
    
    public function scopeOwnerUser($query, int $userId)
    {
        if ($userId > 0) {
            $query->where('owner_user_id', $userId);
        }
    }
}
