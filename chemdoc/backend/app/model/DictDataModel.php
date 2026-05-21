<?php

namespace app\model;

use think\Model;

class DictDataModel extends Model
{
    protected $table = 'sys_dict_data';
    protected $primaryKey = 'data_id';

    public function dictType()
    {
        return $this->belongsTo(DictTypeModel::class, 'dict_id');
    }

    public function getStatusTextAttr($value, $data): string
    {
        return $data['status'] == 1 ? '启用' : '禁用';
    }
}