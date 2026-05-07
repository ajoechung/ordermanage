<?php

namespace app\admin\controller;

class Supplier extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'supplier');
        $this->assign('sub_menu', 'supplier');
        
        $name = input('get.name/s', '');
        $userId = input('get.user_id/d', 0);
        $status = input('get.status/s', '');
        
        $where = [];
        if ($name) {
            $where['name'] = ['like', "%{$name}%"];
        }
        if ($userId) {
            $where['user_id'] = $userId;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        $list = db('supplier')
            ->alias('s')
            ->join('admin_user u', 'u.id = s.user_id', 'LEFT')
            ->field('s.*, u.realname as user_name')
            ->where($where)
            ->order('s.id DESC')
            ->paginate(15);
        
        $users = db('admin_user')->where('status', 1)->select();
        
        $this->assign('list', $list);
        $this->assign('users', $users);
        $this->assign('name', $name);
        $this->assign('userId', $userId);
        $this->assign('status', $status);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'name' => input('post.name/s', ''),
                'contact' => input('post.contact/s', ''),
                'phone' => input('post.phone/s', ''),
                'address' => input('post.address/s', ''),
                'description' => input('post.description/s', ''),
                'user_id' => input('post.user_id/d', 0),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name'])) {
                return json(['code' => 0, 'msg' => '请填写供应商名称']);
            }
            
            db('supplier')->insert($data);
            
            $this->writeLog('供应商管理', '新增供应商：' . $data['name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/supplier/index')]);
        }
        
        $users = db('admin_user')->where('status', 1)->select();
        $this->assign('users', $users);
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'name' => input('post.name/s', ''),
                'contact' => input('post.contact/s', ''),
                'phone' => input('post.phone/s', ''),
                'address' => input('post.address/s', ''),
                'description' => input('post.description/s', ''),
                'user_id' => input('post.user_id/d', 0),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name'])) {
                return json(['code' => 0, 'msg' => '请填写供应商名称']);
            }
            
            db('supplier')->where('id', $id)->update($data);
            
            $this->writeLog('供应商管理', '编辑供应商ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/supplier/index')]);
        }
        
        $info = db('supplier')->find($id);
        $users = db('admin_user')->where('status', 1)->select();
        
        $this->assign('info', $info);
        $this->assign('users', $users);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        db('supplier')->where('id', $id)->delete();
        
        $this->writeLog('供应商管理', '删除供应商ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
