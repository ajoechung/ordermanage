<?php

namespace app\admin\controller;

class Linkman extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'customer');
        $this->assign('sub_menu', 'linkman');
        
        $name = input('get.name/s', '');
        $customerId = input('get.customer_id/d', 0);
        $status = input('get.status/s', '');
        
        $where = [];
        if ($name) {
            $where['l.name'] = ['like', "%{$name}%"];
        }
        if ($customerId) {
            $where['l.customer_id'] = $customerId;
        }
        if ($status !== '') {
            $where['l.status'] = $status;
        }
        
        $list = db('linkman')
            ->alias('l')
            ->join('customer c', 'c.id = l.customer_id', 'LEFT')
            ->field('l.*, c.customer_name')
            ->where($where)
            ->order('l.id DESC')
            ->paginate(15);
        
        $customers = db('customer')->select();
        
        $this->assign('list', $list);
        $this->assign('customers', $customers);
        $this->assign('name', $name);
        $this->assign('customerId', $customerId);
        $this->assign('status', $status);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'name' => input('post.name/s', ''),
                'phone' => input('post.phone/s', ''),
                'position' => input('post.position/s', ''),
                'customer_id' => input('post.customer_id/d', 0),
                'email' => input('post.email/s', ''),
                'remark' => input('post.remark/s', ''),
                'status' => input('post.status/d', 1),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name']) || empty($data['phone']) || empty($data['customer_id'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('linkman')->insert($data);
            
            $this->writeLog('联系人管理', '新增联系人：' . $data['name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/linkman/index')]);
        }
        
        $customers = db('customer')->select();
        $this->assign('customers', $customers);
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'name' => input('post.name/s', ''),
                'phone' => input('post.phone/s', ''),
                'position' => input('post.position/s', ''),
                'customer_id' => input('post.customer_id/d', 0),
                'email' => input('post.email/s', ''),
                'remark' => input('post.remark/s', ''),
                'status' => input('post.status/d', 1),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name']) || empty($data['phone']) || empty($data['customer_id'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('linkman')->where('id', $id)->update($data);
            
            $this->writeLog('联系人管理', '编辑联系人ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/linkman/index')]);
        }
        
        $info = db('linkman')->find($id);
        $customers = db('customer')->select();
        
        $this->assign('info', $info);
        $this->assign('customers', $customers);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        db('linkman')->where('id', $id)->delete();
        
        $this->writeLog('联系人管理', '删除联系人ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
