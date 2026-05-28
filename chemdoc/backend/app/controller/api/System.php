<?php
namespace app\controller\api;

use app\BaseController;
use app\model\AdminUserModel;
use app\model\AuthGroupModel;
use app\model\OperationLogModel;
use app\service\Result;

class System extends BaseController
{
    public function users()
    {
        $params = $this->request->param();
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['username'] ?? '';
        $status = $params['status'] ?? '';
        $groupId = $params['group_id'] ?? '';

        $query = AdminUserModel::with(['authGroupAccess.group']);

        if (!empty($keyword)) {
            $query->where('username', 'like', '%' . $keyword . '%')->whereOr('realname', 'like', '%' . $keyword . '%');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (!empty($groupId)) {
            $query->whereIn('user_id', function ($query) use ($groupId) {
                $query->table('auth_group_access')->where('group_id', $groupId)->column('uid');
            });
        }

        $total = $query->count();
        $list = $query->order('user_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            $item['id'] = $item['user_id'];
            $item['nickname'] = $item['realname'];
            $item['phone'] = $item['mobile'];
            $item['groups'] = [];
            
            if (isset($item['auth_group_access']) && is_array($item['auth_group_access']) && !empty($item['auth_group_access'])) {
                foreach ($item['auth_group_access'] as $access) {
                    if (isset($access['group']) && !empty($access['group'])) {
                        $item['groups'][] = [
                            'id' => $access['group']['id'] ?? '',
                            'name' => $access['group']['name'] ?? ''
                        ];
                    }
                }
            }
            unset($item['auth_group_access']);
        }

        return json(Result::paginate($total, $list, $page, $pageSize));
    }

    public function createUser()
    {
        $data = $this->request->post();

        if (empty($data['username'])) {
            return json(Result::validateError('请输入用户名'));
        }
        if (empty($data['password'])) {
            return json(Result::validateError('请输入密码'));
        }
        if (empty($data['nickname'])) {
            return json(Result::validateError('请输入真实姓名'));
        }

        $exists = AdminUserModel::where('username', $data['username'])->find();
        if ($exists) {
            return json(Result::error('用户名已存在'));
        }

        $salt = bin2hex(random_bytes(16));
        $password = password_hash($data['password'] . $salt, PASSWORD_BCRYPT, ['cost' => 10]);

        $user = AdminUserModel::create([
            'username' => $data['username'],
            'password' => $password,
            'salt' => $salt,
            'realname' => $data['nickname'],
            'mobile' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'status' => 1,
            'create_user_id' => $this->request->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            $this->request->user_id ?? 0,
            $this->request->username ?? '',
            '系统管理',
            '新增用户',
            '新增用户：' . $data['username']
        );

        return json(Result::success(['id' => $user->user_id], '用户创建成功'));
    }

    public function updateUser($id)
    {
        if ($id == 1) {
            return json(Result::error('不能编辑超级管理员用户'));
        }

        $data = $this->request->put();

        $user = AdminUserModel::find($id);
        if (!$user) {
            return json(Result::notFound('用户不存在'));
        }

        $updateData = [];

        if (!empty($data['password'])) {
            $salt = bin2hex(random_bytes(16));
            $updateData['salt'] = $salt;
            $updateData['password'] = password_hash($data['password'] . $salt, PASSWORD_BCRYPT, ['cost' => 10]);
        }

        if (isset($data['nickname'])) {
            $updateData['realname'] = $data['nickname'];
        }
        if (isset($data['phone'])) {
            $updateData['mobile'] = $data['phone'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (!empty($updateData)) {
            $updateData['update_time'] = date('Y-m-d H:i:s');
            $user->save($updateData);
        }

        OperationLogModel::log(
            $this->request->user_id ?? 0,
            $this->request->username ?? '',
            '系统管理',
            '编辑用户',
            '编辑用户：' . $user->username
        );

        return json(Result::success(null, '用户更新成功'));
    }

    public function assignRole()
    {
        try {
            $rawContent = file_get_contents('php://input');
            
            $jsonData = $this->request->json();
            $postData = $this->request->post();
            $data = !empty($jsonData) ? $jsonData : $postData;
            
            if (empty($data)) {
                $data = [];
                if (!empty($rawContent)) {
                    $decoded = json_decode($rawContent, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }
            }
            
            if (empty($data)) {
                return json(Result::validateError('请求数据不能为空'));
            }

            $uid = isset($data['uid']) ? (int)$data['uid'] : 0;
            $groupIds = isset($data['group_ids']) ? $data['group_ids'] : [];

            if (empty($uid)) {
                return json(Result::validateError('用户ID不能为空'));
            }

            if ($uid == 1) {
                return json(Result::error('不能为超级管理员分配角色'));
            }

            \think\facade\Db::name('auth_group_access')->where('uid', $uid)->delete();

            if (!empty($groupIds) && is_array($groupIds)) {
                foreach ($groupIds as $groupId) {
                    $groupId = (int)$groupId;
                    if ($groupId > 0) {
                        \think\facade\Db::name('auth_group_access')->insert([
                            'uid' => $uid,
                            'group_id' => $groupId,
                        ]);
                    }
                }
            }

            try {
                OperationLogModel::log(
                    $this->request->user_id ?? 0,
                    $this->request->username ?? '',
                    '系统管理',
                    '分配角色',
                    '用户ID：' . $uid
                );
            } catch (\Exception $logEx) {
                \think\facade\Log::error('记录操作日志失败: ' . $logEx->getMessage());
            }

            return json(Result::success(null, '角色分配成功'));
        } catch (\Exception $e) {
            \think\facade\Log::error('分配角色失败: ' . $e->getMessage() . ', 堆栈: ' . $e->getTraceAsString());
            return json(Result::error('分配角色失败: ' . $e->getMessage()));
        }
    }

    public function deleteUser($id)
    {
        if ($id == 1) {
            return json(Result::error('不能删除超级管理员'));
        }

        $user = AdminUserModel::find($id);
        if (!$user) {
            return json(Result::notFound('用户不存在'));
        }

        \think\facade\Db::name('auth_group_access')->where('uid', $id)->delete();
        $user->delete();

        OperationLogModel::log(
            $this->request->user_id ?? 0,
            $this->request->username ?? '',
            '系统管理',
            '删除用户',
            '删除用户：' . $user->username
        );

        return json(Result::success(null, '用户删除成功'));
    }

    public function groups()
    {
        $groups = AuthGroupModel::order('id', 'asc')->select()->toArray();
        
        foreach ($groups as &$group) {
            $group['user_count'] = \think\facade\Db::name('auth_group_access')->where('group_id', $group['id'])->count();
        }
        
        return json(Result::success($groups));
    }

    public function getUsersByRole($id)
    {
        $userIds = \think\facade\Db::name('auth_group_access')
            ->where('group_id', $id)
            ->column('uid');
        
        if (empty($userIds)) {
            return json(Result::success([]));
        }
        
        $users = AdminUserModel::whereIn('user_id', $userIds)
            ->select()
            ->toArray();
        
        foreach ($users as &$user) {
            $user['user_id'] = $user['user_id'];
            $user['realname'] = $user['realname'];
            $user['phone'] = $user['mobile'];
        }
        
        return json(Result::success($users));
    }

    public function createGroup()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入角色名称'));
        }

        $group = AuthGroupModel::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? '',
            'description' => $data['description'] ?? '',
            'rules' => isset($data['rules']) ? implode(',', $data['rules']) : '',
            'status' => $data['status'] ?? 1,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        return json(Result::success(['id' => $group->id], '角色创建成功'));
    }

    public function updateGroup($id)
    {
        $data = $this->request->put();

        $group = AuthGroupModel::find($id);
        if (!$group) {
            return json(Result::notFound('角色不存在'));
        }

        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['rules'])) {
            if (is_array($data['rules'])) {
                $updateData['rules'] = implode(',', $data['rules']);
            } else {
                $updateData['rules'] = $data['rules'];
            }
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $group->save($updateData);

        return json(Result::success(null, '角色更新成功'));
    }

    public function deleteGroup($id)
    {
        if ($id == 1) {
            return json(Result::error('不能删除超级管理员角色'));
        }

        $group = AuthGroupModel::find($id);
        if (!$group) {
            return json(Result::notFound('角色不存在'));
        }

        $hasUsers = \think\facade\Db::name('auth_group_access')->where('group_id', $id)->count();
        if ($hasUsers > 0) {
            return json(Result::error('该角色存在关联用户，无法删除'));
        }

        $group->delete();

        return json(Result::success(null, '角色删除成功'));
    }

    public function rules()
    {
        $params = $this->request->param();
        
        $query = \think\facade\Db::name('auth_rule');
        
        if (!empty($params['title'])) {
            $query->where('title', 'like', '%' . $params['title'] . '%');
        }
        
        $list = $query->order('sort', 'asc')
            ->select()
            ->toArray();
        
        $tree = $this->buildTree($list);
        
        return json(Result::success($tree));
    }

    public function createRule()
    {
        $data = $this->request->post();
        
        if (empty($data['title'])) {
            return json(Result::validateError('请输入权限名称'));
        }
        if (empty($data['name'])) {
            return json(Result::validateError('请输入规则标识'));
        }
        
        $id = \think\facade\Db::name('auth_rule')->insertGetId([
            'name' => $data['name'],
            'title' => $data['title'],
            'type' => $data['type'] ?? 1,
            'pid' => $data['pid'] ?? 0,
            'status' => $data['status'] ?? 1,
            'is_menu' => $data['is_menu'] ?? 0,
            'condition' => $data['condition'] ?? '',
            'remark' => $data['remark'] ?? '',
            'sort' => $data['sort'] ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
        
        return json(Result::success(['id' => $id], '权限创建成功'));
    }

    public function updateRule($id)
    {
        $data = $this->request->put();
        
        $rule = \think\facade\Db::name('auth_rule')->find($id);
        if (!$rule) {
            return json(Result::notFound('权限不存在'));
        }
        
        $updateData = [];
        
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }
        if (isset($data['pid'])) {
            $updateData['pid'] = $data['pid'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }
        if (isset($data['is_menu'])) {
            $updateData['is_menu'] = $data['is_menu'];
        }
        if (isset($data['condition'])) {
            $updateData['condition'] = $data['condition'];
        }
        if (isset($data['remark'])) {
            $updateData['remark'] = $data['remark'];
        }
        if (isset($data['sort'])) {
            $updateData['sort'] = $data['sort'];
        }
        
        if (!empty($updateData)) {
            $updateData['update_time'] = date('Y-m-d H:i:s');
            \think\facade\Db::name('auth_rule')->where('id', $id)->update($updateData);
        }
        
        return json(Result::success(null, '权限更新成功'));
    }

    public function deleteRule($id)
    {
        $rule = \think\facade\Db::name('auth_rule')->find($id);
        if (!$rule) {
            return json(Result::notFound('权限不存在'));
        }
        
        $hasChildren = \think\facade\Db::name('auth_rule')->where('pid', $id)->count();
        if ($hasChildren > 0) {
            return json(Result::error('存在子权限，无法删除'));
        }
        
        $usedInRoles = \think\facade\Db::name('auth_group')
            ->where('rules', 'like', '%' . $id . '%')
            ->whereOr('rules', '=', '*')
            ->count();
        
        if ($usedInRoles > 0) {
            return json(Result::error('该权限已被角色使用，无法删除'));
        }
        
        \think\facade\Db::name('auth_rule')->where('id', $id)->delete();
        
        return json(Result::success(null, '权限删除成功'));
    }

    public function ruleTree()
    {
        $rules = \think\facade\Db::name('auth_rule')
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        $tree = $this->buildTree($rules);

        return json(Result::success($tree));
    }

    protected function buildTree(array $data, int $pid = 0): array
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item['pid'] == $pid) {
                $children = $this->buildTree($data, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}