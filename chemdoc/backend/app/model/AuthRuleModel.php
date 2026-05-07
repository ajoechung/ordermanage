<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;

/**
 * 权限规则模型
 */
class AuthRuleModel extends BaseModel
{
    protected $table = 'auth_rule';
    protected $primaryKey = 'id';

    protected $type = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'pid');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'pid');
    }

    public function getStatusTextAttr($value, $data): string
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    public function getIsMenuTextAttr($value, $data): string
    {
        return (isset($data['is_menu']) && $data['is_menu'] == 1) ? '是' : '否';
    }

    public function scopeMenu($query)
    {
        $query->where('is_menu', 1)->where('status', 1);
    }

    public function scopePid($query, int $pid)
    {
        $query->where('pid', $pid);
    }

    public function scopeStatus($query, int $status = 1)
    {
        $query->where('status', $status);
    }
}
