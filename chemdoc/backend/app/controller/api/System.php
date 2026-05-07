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
        $keyword = $params['keyword'] ?? '';
        $status = $params['status'] ?? '';
        $groupId = $params['group_id'] ?? '';

        $query = AdminUserModel::with(['authGroupAccess.group']);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (!empty($groupId)) {
            $query->scope('groupId', (int)$groupId);
        }

        $total = $query->count();
        $list = $query->order('user_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['auth_group_access']) && !empty($item['auth_group_access'])) {
                $item['group_names'] = array_column($item['auth_group_access'], 'name');
                unset($item['auth_group_access']);
            }
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
        if (empty($data['realname'])) {
            return json(Result::validateError('请输入真实姓名'));
        }
        if (empty($data['group_ids'])) {
            return json(Result::validateError('请选择用户角色'));
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
            'realname' => $data['realname'],
            'mobile' => $data['mobile'] ?? '',
            'email' => $data['email'] ?? '',
            'status' => $data['status'] ?? 1,
            'create_user_id' => $this->request->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        foreach ($data['group_ids'] as $groupId) {
            \think\facade\Db::name('auth_group_access')->insert([
                'uid' => $user->user_id,
                'group_id' => $groupId,
            ]);
        }

        OperationLogModel::log(
            $this->request->user_id ?? 0,
            $this->request->username ?? '',
            '系统管理',
            '新增用户',
            '新增用户：' . $data['username']
        );

        return json(Result::success(['user_id' => $user->user_id], '用户创建成功'));
    }

    public function updateUser($id)
    {
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

        $fields = ['realname', 'mobile', 'email', 'status'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($data['group_ids'])) {
            \think\facade\Db::name('auth_group_access')->where('uid', $id)->delete();
            foreach ($data['group_ids'] as $groupId) {
                \think\facade\Db::name('auth_group_access')->insert([
                    'uid' => $id,
                    'group_id' => $groupId,
                ]);
            }
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
        return json(Result::success($groups));
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
            $updateData['rules'] = implode(',', $data['rules']);
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
