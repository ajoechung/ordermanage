<?php
declare(strict_types=1);

namespace app\model;

/**
 * 采购单明细模型
 */
class PurchaseOrderItemModel extends BaseModel
{
    protected $table = 'purchase_order_item';
    protected $primaryKey = 'purchase_item_id';

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
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

    public function getArrivedQuantityAttr($value): float
    {
        return round((float)$value, 3);
    }
}
