<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\BelongsTo;

/**
 * 用户角色关联模型
 */
class AuthGroupAccessModel extends BaseModel
{
    protected $table = 'auth_group_access';
    protected $primaryKey = 'id';

    public function user(): BelongsTo
    {
        return $this->belongsTo(AdminUserModel::class, 'uid', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AuthGroupModel::class, 'group_id', 'id');
    }
}
