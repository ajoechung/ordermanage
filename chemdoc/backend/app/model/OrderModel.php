<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;
use think\model\Relation\HasMany;

/**
 * 订单模型
 */
class OrderModel extends BaseModel
{
    protected $table = 'order';
    protected $primaryKey = 'order_id';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ContactModel::class, 'contact_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderModel::class, 'purchase_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(OrderInvoiceModel::class, 'order_id');
    }

    public function getOrderStatusTextAttr($value, $data): string
    {
        $status = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消',
        ];
        return $status[$data['order_status']] ?? '未知';
    }

    public function getInvoiceStatusTextAttr($value, $data): string
    {
        $status = [
            1 => '未开票',
            2 => '开票中',
            3 => '已开票',
            4 => '已收票',
        ];
        return $status[$data['invoice_status']] ?? '未知';
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('order_no', "%{$keyword}%");
            })->whereOr(function ($q) use ($keyword) {
                $q->hasWhere('customer', ['name' => ['like', "%{$keyword}%"]]);
            });
        }
    }

    public function scopeCustomerId($query, int $customerId)
    {
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
    }

    public function scopeOrderStatus($query, int $status)
    {
        if ($status > 0) {
            $query->where('order_status', $status);
        }
    }

    public function scopeInvoiceStatus($query, int $status)
    {
        if ($status > 0) {
            $query->where('invoice_status', $status);
        }
    }

    public function scopeDateRange($query, array $dateRange)
    {
        if (!empty($dateRange) && count($dateRange) == 2) {
            $query->whereBetweenTime('order_time', $dateRange[0], $dateRange[1]);
        }
    }

    public function scopeCreateUser($query, int $userId)
    {
        if ($userId > 0) {
            $query->where('create_user_id', $userId);
        }
    }
}
