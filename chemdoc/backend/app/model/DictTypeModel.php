<?php

namespace app\model;

use think\Model;

class DictTypeModel extends Model
{
    protected $table = 'sys_dict_type';
    protected $primaryKey = 'dict_id';

    public function dictData()
    {
        return $this->hasMany(DictDataModel::class, 'dict_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        return $data['status'] == 1 ? '启用' : '禁用';
    }
}