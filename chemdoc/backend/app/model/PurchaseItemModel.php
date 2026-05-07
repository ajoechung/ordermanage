<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;

class PurchaseItemModel extends BaseModel
{
    protected $table = 'purchase_item';
    protected $primaryKey = 'item_id';

    public function purchase()
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
