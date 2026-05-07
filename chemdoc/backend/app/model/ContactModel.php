<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;

/**
 * 联系人模型
 */
class ContactModel extends BaseModel
{
    protected $table = 'contact';
    protected $primaryKey = 'contact_id';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function createUser()
    {
        return $this->belongsTo(AdminUserModel::class, 'create_user_id', 'user_id');
    }

    public function getGenderTextAttr($value, $data): string
    {
        $gender = [0 => '女', 1 => '男'];
        return $gender[$data['gender']] ?? '未知';
    }

    public function getIsDefaultTextAttr($value, $data): string
    {
        return (isset($data['is_default']) && $data['is_default'] == 1) ? '是' : '否';
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                    ->whereOr('mobile', 'like', "%{$keyword}%")
                    ->whereOr('email', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeCustomerId($query, int $customerId)
    {
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
    }

    public function scopeIsDefault($query, bool $isDefault = true)
    {
        $query->where('is_default', $isDefault ? 1 : 0);
    }
}
