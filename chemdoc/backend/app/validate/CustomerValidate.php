<?php
declare(strict_types=1);

namespace app\validate;

use think\Validate;

class CustomerValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:200',
        'code' => 'alphaNum|max:50',
        'industry' => 'max:50',
        'source' => 'max:50',
        'owner_user_id' => 'number',
        'address' => 'max:500',
        'scale' => 'max:20',
        'annual_revenue' => 'float|egt:0',
        'description' => 'max:2000',
        'status' => 'in:0,1',
        'level' => 'in:1,2,3',
    ];

    protected $message = [
        'name.require' => '客户名称不能为空',
        'name.max' => '客户名称最多200个字符',
        'code.alphaNum' => '客户编码只能是字母或数字',
        'code.max' => '客户编码最多50个字符',
        'industry.max' => '行业分类最多50个字符',
        'owner_user_id.number' => '所属业务员ID必须是数字',
        'address.max' => '地址最多500个字符',
        'scale.max' => '客户规模最多20个字符',
        'annual_revenue.float' => '年营业额必须是数字',
        'description.max' => '描述最多2000个字符',
    ];

    protected $scene = [
        'create' => ['name'],
        'update' => ['name'],
    ];
}
