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

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
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
}
