<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsToMany;

/**
 * 角色模型
 */
class AuthGroupModel extends BaseModel
{
    protected $table = 'auth_group';
    protected $primaryKey = 'id';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminUserModel::class,
            'auth_group_access',
            'uid'
        )->wherePivot('group_id', $this->id);
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    public function getRulesListAttr($value, $data): array
    {
        if (empty($data['rules'])) {
            return [];
        }
        return array_filter(explode(',', $data['rules']));
    }

    public function scopeStatus($query, int $status = 1)
    {
        $query->where('status', $status);
    }
}
