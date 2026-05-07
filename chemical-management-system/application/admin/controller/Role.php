<?php

namespace app\admin\controller;

class Role extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'system');
        $this->assign('sub_menu', 'role');
        
        $list = db('admin_role')->order('id ASC')->select();
        
        foreach ($list as &$role) {
            $userCount = db('admin_user_role')->where('role_id', $role['id'])->count();
            $role['user_count'] = $userCount;
        }
        unset($role);
        
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'role_name' => input('post.role_name/s', ''),
                'role_code' => input('post.role_code/s', ''),
                'description' => input('post.description/s', ''),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['role_name']) || empty($data['role_code'])) {
                return json(['code' => 0, 'msg' => '请填写角色名称和标识']);
            }
            
            if (db('admin_role')->where('role_code', $data['role_code'])->find()) {
                return json(['code' => 0, 'msg' => '角色标识已存在']);
            }
            
            $roleId = db('admin_role')->insertGetId($data);
            
            $authIds = input('post.auth_ids/a', []);
            if ($authIds) {
                foreach ($authIds as $authId) {
                    db('admin_role_auth')->insert([
                        'role_id' => $roleId,
                        'auth_id' => $authId
                    ]);
                }
            }
            
            $this->writeLog('角色管理', '新增角色：' . $data['role_name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/role/index')]);
        }
        
        $auths = $this->getAuthTree();
        $this->assign('auths', $auths);
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'role_name' => input('post.role_name/s', ''),
                'role_code' => input('post.role_code/s', ''),
                'description' => input('post.description/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['role_name']) || empty($data['role_code'])) {
                return json(['code' => 0, 'msg' => '请填写角色名称和标识']);
            }
            
            $exists = db('admin_role')->where('role_code', $data['role_code'])->where('id', '<>', $id)->find();
            if ($exists) {
                return json(['code' => 0, 'msg' => '角色标识已存在']);
            }
            
            db('admin_role')->where('id', $id)->update($data);
            
            db('admin_role_auth')->where('role_id', $id)->delete();
            $authIds = input('post.auth_ids/a', []);
            if ($authIds) {
                foreach ($authIds as $authId) {
                    db('admin_role_auth')->insert([
                        'role_id' => $id,
                        'auth_id' => $authId
                    ]);
                }
            }
            
            $this->writeLog('角色管理', '编辑角色ID：' . $id, '编辑');
            
            $userIds = db('admin_user_role')->where('role_id', $id)->column('user_id');
            foreach ($userIds as $userId) {
                cache('user_auths_' . $userId, null);
            }
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/role/index')]);
        }
        
        $info = db('admin_role')->find($id);
        $roleAuths = db('admin_role_auth')->where('role_id', $id)->column('auth_id');
        $info['auth_ids'] = $roleAuths ?: [];
        
        $auths = $this->getAuthTree();
        
        $this->assign('info', $info);
        $this->assign('auths', $auths);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        if ($id == 1) {
            return json(['code' => 0, 'msg' => '不能删除超级管理员角色']);
        }
        
        $userCount = db('admin_user_role')->where('role_id', $id)->count();
        if ($userCount > 0) {
            return json(['code' => 0, 'msg' => '该角色下有用户，无法删除']);
        }
        
        db('admin_role')->where('id', $id)->delete();
        db('admin_role_auth')->where('role_id', $id)->delete();
        
        $this->writeLog('角色管理', '删除角色ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    private function getAuthTree($parentId = 0, $level = 0)
    {
        $auths = db('admin_auth')
            ->where('parent_id', $parentId)
            ->order('sort ASC')
            ->select();
        
        foreach ($auths as &$auth) {
            $auth['level'] = $level;
            $auth['children'] = $this->getAuthTree($auth['id'], $level + 1);
        }
        unset($auth);
        
        return $auths;
    }
}
