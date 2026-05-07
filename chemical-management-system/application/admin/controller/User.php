<?php

namespace app\admin\controller;

class User extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'system');
        $this->assign('sub_menu', 'user');
        
        $username = input('get.username/s', '');
        $roleId = input('get.role_id/d', 0);
        $status = input('get.status/s', '');
        
        $where = [];
        if ($username) {
            $where['username'] = ['like', "%{$username}%"];
        }
        if ($roleId) {
            $where['ur.role_id'] = $roleId;
        }
        if ($status !== '') {
            $where['u.status'] = $status;
        }
        
        $list = db('admin_user')
            ->alias('u')
            ->join('admin_user_role ur', 'ur.user_id = u.id', 'LEFT')
            ->join('admin_role r', 'r.id = ur.role_id', 'LEFT')
            ->field('u.*, r.role_name, GROUP_CONCAT(r.role_name) as role_names')
            ->where($where)
            ->group('u.id')
            ->order('u.id DESC')
            ->paginate(15);
        
        $roles = db('admin_role')->select();
        
        $this->assign('list', $list);
        $this->assign('roles', $roles);
        $this->assign('username', $username);
        $this->assign('roleId', $roleId);
        $this->assign('status', $status);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'username' => input('post.username/s', ''),
                'password' => input('post.password/s', ''),
                'realname' => input('post.realname/s', ''),
                'phone' => input('post.phone/s', ''),
                'email' => input('post.email/s', ''),
                'role_id' => input('post.role_id/d', 0),
                'status' => input('post.status/d', 1),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['username']) || empty($data['password']) || empty($data['realname'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            if (db('admin_user')->where('username', $data['username'])->find()) {
                return json(['code' => 0, 'msg' => '用户名已存在']);
            }
            
            $data['password'] = md5($data['password']);
            
            $userId = db('admin_user')->insertGetId($data);
            
            if ($userId && $data['role_id']) {
                db('admin_user_role')->insert([
                    'user_id' => $userId,
                    'role_id' => $data['role_id']
                ]);
            }
            
            $this->writeLog('用户管理', '新增用户：' . $data['username'], '新增');
            
            cache('user_auths_' . $userId, null);
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/user/index')]);
        }
        
        $roles = db('admin_role')->select();
        $this->assign('roles', $roles);
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'realname' => input('post.realname/s', ''),
                'phone' => input('post.phone/s', ''),
                'email' => input('post.email/s', ''),
                'role_id' => input('post.role_id/d', 0),
                'status' => input('post.status/d', 1),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['realname'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            $password = input('post.password/s', '');
            if ($password) {
                $data['password'] = md5($password);
            }
            
            db('admin_user')->where('id', $id)->update($data);
            
            db('admin_user_role')->where('user_id', $id)->delete();
            if ($data['role_id']) {
                db('admin_user_role')->insert([
                    'user_id' => $id,
                    'role_id' => $data['role_id']
                ]);
            }
            
            $this->writeLog('用户管理', '编辑用户ID：' . $id, '编辑');
            
            cache('user_auths_' . $id, null);
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/user/index')]);
        }
        
        $info = db('admin_user')->find($id);
        $userRole = db('admin_user_role')->where('user_id', $id)->find();
        $info['role_id'] = $userRole ? $userRole['role_id'] : 0;
        
        $roles = db('admin_role')->select();
        
        $this->assign('info', $info);
        $this->assign('roles', $roles);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        if ($id == 1) {
            return json(['code' => 0, 'msg' => '不能删除超级管理员']);
        }
        
        db('admin_user')->where('id', $id)->delete();
        db('admin_user_role')->where('user_id', $id)->delete();
        
        $this->writeLog('用户管理', '删除用户ID：' . $id, '删除');
        
        cache('user_auths_' . $id, null);
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    public function resetPwd()
    {
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $newPwd = input('post.password/s', '');
            
            if (empty($newPwd) || strlen($newPwd) < 6) {
                return json(['code' => 0, 'msg' => '密码长度不能少于6位']);
            }
            
            db('admin_user')->where('id', $id)->update([
                'password' => md5($newPwd),
                'update_time' => date('Y-m-d H:i:s')
            ]);
            
            $this->writeLog('用户管理', '重置用户密码ID：' . $id, '重置密码');
            
            return json(['code' => 1, 'msg' => '密码重置成功']);
        }
        
        return $this->fetch();
    }
}
