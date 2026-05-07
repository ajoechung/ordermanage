<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;
use think\model\Relation\HasMany;

/**
 * 采购单模型
 */
class PurchaseOrderModel extends BaseModel
{
    protected $table = 'purchase_order';
    protected $primaryKey = 'purchase_order_id';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }

    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItemModel::class, 'purchase_order_id');
    }

    public function getPurchaseStatusTextAttr($value, $data): string
    {
        $status = [
            1 => '待确认',
            2 => '已确认',
            3 => '采购中',
            4 => '部分到货',
            5 => '已到货',
            6 => '已完成',
            7 => '已取消',
        ];
        return $status[$data['purchase_status']] ?? '未知';
    }

    public function getInvoiceStatusTextAttr($value, $data): string
    {
        $status = [
            1 => '未收票',
            2 => '收票中',
            3 => '已收票',
            4 => '已认证',
        ];
        return $status[$data['invoice_status']] ?? '未知';
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('purchase_no', "%{$keyword}%");
            })->whereOr(function ($q) use ($keyword) {
                $q->hasWhere('supplier', ['name' => ['like', "%{$keyword}%"]]);
            });
        }
    }

    public function scopeSupplierId($query, int $supplierId)
    {
        if ($supplierId > 0) {
            $query->where('supplier_id', $supplierId);
        }
    }

    public function scopePurchaseStatus($query, int $status)
    {
        if ($status > 0) {
            $query->where('purchase_status', $status);
        }
    }

    public function scopeOrderId($query, int $orderId)
    {
        if ($orderId > 0) {
            $query->where('order_id', $orderId);
        }
    }

    public function scopeDateRange($query, array $dateRange)
    {
        if (!empty($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('order_time', $dateRange[0], $dateRange[1]);
        }
    }
}
