<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;
use think\model\Relation\HasMany;

class PurchaseOrderModel extends BaseModel
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'purchase_id';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItemModel::class, 'purchase_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [
            1 => '草稿',
            2 => '已提交',
            3 => '已确认',
            4 => '已入库',
            5 => '已完成',
            6 => '已取消',
        ];
        return $status[$data['status']] ?? '未知';
    }
}
