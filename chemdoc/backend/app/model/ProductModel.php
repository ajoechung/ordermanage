<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;
use think\model\Relation\HasMany;

/**
 * 产品模型
 */
class ProductModel extends BaseModel
{
    protected $table = 'product';
    protected $primaryKey = 'product_id';

    public function category()
    {
        return $this->belongsTo(ProductCategoryModel::class, 'category_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'product_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItemModel::class, 'product_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [0 => '下架', 1 => '上架'];
        return $status[$data['status']] ?? '未知';
    }

    public function getPriceAttr($value): float
    {
        return round((float)$value, 2);
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('code', 'like', "%{$keyword}%")
                    ->whereOr('spec', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeCategoryId($query, int $categoryId)
    {
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
    }

    public function scopeOnSale($query)
    {
        $query->where('status', 1);
    }
}
