<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

abstract class BaseModel extends Model
{
    protected $type = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
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

    public function getStatusAttr($value): string
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$value] ?? '未知';
    }
}
