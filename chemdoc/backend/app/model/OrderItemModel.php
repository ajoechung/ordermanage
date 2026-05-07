<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;

/**
 * 订单明细模型
 */
class OrderItemModel extends BaseModel
{
    protected $table = 'order_item';
    protected $primaryKey = 'order_item_id';

    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }

    public function getSubtotalAttr($value): float
    {
        return round((float)$value, 2);
    }

    public function getUnitPriceAttr($value): float
    {
        return round((float)$value, 2);
    }

    public function getQuantityAttr($value): float
    {
        return round((float)$value, 3);
    }

    public function getShippedQuantityAttr($value): float
    {
        return round((float)$value, 3);
    }
}
