<?php
namespace app\service;

use think\facade\Db;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    public function login(string $username, string $password): array
    {
        $user = Db::name('admin_user')
            ->where('username', $username)
            ->where('status', 1)
            ->find();

        if (!$user) {
            return Result::error('用户不存在或已被禁用');
        }

        if (!password_verify($password, $user['password'])) {
            return Result::error('密码错误');
        }

        $groups = Db::name('auth_group_access')
            ->alias('aga')
            ->join('auth_group ag', 'aga.group_id = ag.id')
            ->where('aga.uid', $user['user_id'])
            ->where('ag.status', 1)
            ->column('ag.id');

        $token = $this->generateToken($user, $groups);

        Db::name('admin_user')
            ->where('user_id', $user['user_id'])
            ->update([
                'last_login_time' => date('Y-m-d H:i:s'),
                'last_login_ip' => request()->ip(),
            ]);

        $this->logOperation($user['user_id'], '登录', '用户登录系统');

        return Result::success([
            'token' => $token,
            'user_info' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'realname' => $user['realname'],
                'avatar' => $user['avatar'],
                'groups' => $groups,
            ],
        ], '登录成功');
    }

    public function logout(): array
    {
        $userId = request()->user_id ?? 0;
        if ($userId > 0) {
            $this->logOperation($userId, '注销', '用户退出系统');
        }

        return Result::success(null, '退出成功');
    }

    public function getUserInfo(int $userId): array
    {
        $user = Db::name('admin_user')
            ->where('user_id', $userId)
            ->find();

        if (!$user) {
            return Result::error('用户不存在');
        }

        $groups = Db::name('auth_group_access')
            ->alias('aga')
            ->join('auth_group ag', 'aga.group_id = ag.id')
            ->where('aga.uid', $userId)
            ->where('ag.status', 1)
            ->select()
            ->toArray();

        $groupNames = array_column($groups, 'name');

        return Result::success([
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'realname' => $user['realname'],
            'mobile' => $user['mobile'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
            'groups' => $groupNames,
            'last_login_time' => $user['last_login_time'],
        ]);
    }

    protected function generateToken(array $user, array $groups): string
    {
        $config = config('jwt.');

        $payload = [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'groups' => $groups,
            'iat' => time(),
            'exp' => time() + $config['expire'],
        ];

        return JWT::encode($payload, $config['secret'], $config['algo']);
    }

    protected function logOperation(int $userId, string $action, string $description): void
    {
        Db::name('operation_log')->insert([
            'user_id' => $userId,
            'username' => Db::name('admin_user')->where('user_id', $userId)->value('username'),
            'module' => '系统登录',
            'action' => $action,
            'description' => $description,
            'request_method' => request()->method(),
            'request_url' => request()->url(true),
            'client_ip' => request()->ip(),
            'user_agent' => request()->header('user-agent', ''),
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }
}
