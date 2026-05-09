<?php
declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

abstract class BaseModel extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = null;

    protected $type = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    protected $hidden = [];

    protected $append = [];

    protected $auto = [];

    protected $insert = [];

    protected $update = [];

    protected $readonly = [];

    public function setCreateTimeAttr($value)
    {
        return $value ?? date('Y-m-d H:i:s');
    }

    public function setUpdateTimeAttr($value)
    {
        return date('Y-m-d H:i:s');
    }

    protected function scopeStatus(array $query = []): void
    {
        $this->where('status', '=', 1);
    }

    public function getStatusAttr($value): string
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$value] ?? '未知';
    }
}
