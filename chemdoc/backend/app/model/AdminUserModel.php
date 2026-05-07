<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\HasMany;
use think\model\Relation\BelongsToMany;

/**
 * 管理员用户模型
 */
class AdminUserModel extends BaseModel
{
    protected $table = 'admin_user';
    protected $primaryKey = 'user_id';

    protected $hidden = ['password', 'salt', 'delete_time'];

    protected $append = ['status_text'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthGroupModel::class,
            'auth_group_access',
            'group_id'
        )->wherePivot('uid', $this->user_id);
    }

    public function authGroupAccess()
    {
        return $this->hasMany(AuthGroupAccessModel::class, 'uid');
    }

    public function getStatusTextAttr($value, $data): string
    {
        return (isset($data['status']) && $data['status'] == 1) ? '启用' : '禁用';
    }

    public function setPasswordAttr($value, $data): string
    {
        if (!isset($data['salt'])) {
            $data['salt'] = $this->generateSalt();
        }
        return password_hash($value . $data['salt'], PASSWORD_BCRYPT, ['cost' => 10]);
    }

    protected function generateSalt(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password . $this->salt, $this->password);
    }

    public function scopeNormal($query)
    {
        $query->where('status', 1)->whereNull('delete_time');
    }

    public function scopeKeyword($query, string $keyword)
    {
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('username', "%{$keyword}%")
                    ->whereOr('realname', 'like', "%{$keyword}%")
                    ->whereOr('mobile', 'like', "%{$keyword}%");
            });
        }
    }

    public function scopeGroupId($query, int $groupId)
    {
        if ($groupId > 0) {
            $query->whereIn('user_id', function ($q) use ($groupId) {
                $q->name('auth_group_access')
                    ->where('group_id', $groupId)
                    ->column('uid');
            });
        }
    }
}
