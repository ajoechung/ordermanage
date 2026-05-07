<?php

namespace app\admin\controller;

class Auth extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'system');
        $this->assign('sub_menu', 'auth');
        
        $auths = $this->getAuthTree();
        $this->assign('auths', $auths);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'auth_name' => input('post.auth_name/s', ''),
                'auth_code' => input('post.auth_code/s', ''),
                'parent_id' => input('post.parent_id/d', 0),
                'auth_type' => input('post.auth_type/d', 1),
                'sort' => input('post.sort/d', 0),
                'is_show' => input('post.is_show/d', 1),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['auth_name']) || empty($data['auth_code'])) {
                return json(['code' => 0, 'msg' => '请填写权限名称和标识']);
            }
            
            if (db('admin_auth')->where('auth_code', $data['auth_code'])->find()) {
                return json(['code' => 0, 'msg' => '权限标识已存在']);
            }
            
            db('admin_auth')->insert($data);
            
            $this->writeLog('权限管理', '新增权限：' . $data['auth_name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/auth/index')]);
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
                'auth_name' => input('post.auth_name/s', ''),
                'auth_code' => input('post.auth_code/s', ''),
                'parent_id' => input('post.parent_id/d', 0),
                'auth_type' => input('post.auth_type/d', 1),
                'sort' => input('post.sort/d', 0),
                'is_show' => input('post.is_show/d', 1),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['auth_name']) || empty($data['auth_code'])) {
                return json(['code' => 0, 'msg' => '请填写权限名称和标识']);
            }
            
            $exists = db('admin_auth')->where('auth_code', $data['auth_code'])->where('id', '<>', $id)->find();
            if ($exists) {
                return json(['code' => 0, 'msg' => '权限标识已存在']);
            }
            
            if ($data['parent_id'] == $id) {
                return json(['code' => 0, 'msg' => '不能将自己设为父级']);
            }
            
            db('admin_auth')->where('id', $id)->update($data);
            
            $this->writeLog('权限管理', '编辑权限ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/auth/index')]);
        }
        
        $info = db('admin_auth')->find($id);
        $auths = $this->getAuthTree();
        
        $this->assign('info', $info);
        $this->assign('auths', $auths);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        $childCount = db('admin_auth')->where('parent_id', $id)->count();
        if ($childCount > 0) {
            return json(['code' => 0, 'msg' => '该权限下有子权限，无法删除']);
        }
        
        db('admin_auth')->where('id', $id)->delete();
        db('admin_role_auth')->where('auth_id', $id)->delete();
        
        $this->writeLog('权限管理', '删除权限ID：' . $id, '删除');
        
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
