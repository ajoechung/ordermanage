<?php

namespace app\admin\controller;

class Customer extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'customer');
        $this->assign('sub_menu', 'customer');
        
        $customerName = input('get.customer_name/s', '');
        $industry = input('get.industry/s', '');
        $userId = input('get.user_id/d', 0);
        $status = input('get.status/s', '');
        
        $where = [];
        if ($customerName) {
            $where['customer_name'] = ['like', "%{$customerName}%"];
        }
        if ($industry) {
            $where['industry'] = $industry;
        }
        if ($userId) {
            $where['user_id'] = $userId;
        }
        if ($status !== '') {
            $where['status'] = $status;
        }
        
        if ($this->adminUsername != 'admin') {
            $where['user_id'] = $this->adminUserId;
        }
        
        $list = db('customer')
            ->alias('c')
            ->join('admin_user u', 'u.id = c.user_id', 'LEFT')
            ->field('c.*, u.realname as user_name')
            ->where($where)
            ->order('c.id DESC')
            ->paginate(15);
        
        $users = db('admin_user')->where('status', 1)->select();
        
        $this->assign('list', $list);
        $this->assign('users', $users);
        $this->assign('customerName', $customerName);
        $this->assign('industry', $industry);
        $this->assign('userId', $userId);
        $this->assign('status', $status);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'customer_name' => input('post.customer_name/s', ''),
                'industry' => input('post.industry/s', ''),
                'source' => input('post.source/s', ''),
                'user_id' => input('post.user_id/d', 0),
                'address' => input('post.address/s', ''),
                'description' => input('post.description/s', ''),
                'phone' => input('post.phone/s', ''),
                'email' => input('post.email/s', ''),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            // 必填项验证
            if (empty($data['customer_name'])) {
                return json(['code' => 0, 'msg' => '请填写客户名称']);
            }
            if (empty($data['user_id'])) {
                return json(['code' => 0, 'msg' => '请选择负责人']);
            }
            
            // 检查重复客户
            $exists = db('customer')
                ->where('customer_name', $data['customer_name'])
                ->find();
            if ($exists) {
                return json(['code' => 0, 'msg' => '该客户名称已存在']);
            }
            
            db('customer')->insert($data);
            
            $this->writeLog('客户管理', '新增客户：' . $data['customer_name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/customer/index')]);
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
                'customer_name' => input('post.customer_name/s', ''),
                'industry' => input('post.industry/s', ''),
                'source' => input('post.source/s', ''),
                'user_id' => input('post.user_id/d', 0),
                'address' => input('post.address/s', ''),
                'description' => input('post.description/s', ''),
                'phone' => input('post.phone/s', ''),
                'email' => input('post.email/s', ''),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            // 必填项验证
            if (empty($data['customer_name'])) {
                return json(['code' => 0, 'msg' => '请填写客户名称']);
            }
            if (empty($data['user_id'])) {
                return json(['code' => 0, 'msg' => '请选择负责人']);
            }
            
            // 检查重复客户（排除自身）
            $exists = db('customer')
                ->where('customer_name', $data['customer_name'])
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return json(['code' => 0, 'msg' => '该客户名称已存在']);
            }
            
            db('customer')->where('id', $id)->update($data);
            
            $this->writeLog('客户管理', '编辑客户ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/customer/index')]);
        }
        
        $info = db('customer')->find($id);
        $users = db('admin_user')->where('status', 1)->select();
        
        $this->assign('info', $info);
        $this->assign('users', $users);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        // 检查是否有关联订单
        $orderCount = db('orders')->where('customer_id', $id)->count();
        if ($orderCount > 0) {
            return json(['code' => 0, 'msg' => '该客户下存在 ' . $orderCount . ' 个订单，无法直接删除']);
        }
        
        // 级联删除关联数据
        db('linkman')->where('customer_id', $id)->delete();
        db('customer_follow')->where('customer_id', $id)->delete();
        db('customer')->where('id', $id)->delete();
        
        $this->writeLog('客户管理', '删除客户ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    public function detail()
    {
        $id = input('get.id/d', 0);
        
        $info = db('customer')
            ->alias('c')
            ->join('admin_user u', 'u.id = c.user_id', 'LEFT')
            ->field('c.*, u.realname as user_name')
            ->where('c.id', $id)
            ->find();
        
        $linkmans = db('linkman')
            ->where('customer_id', $id)
            ->order('id DESC')
            ->select();
        
        $follows = db('customer_follow')
            ->alias('f')
            ->join('admin_user u', 'u.id = f.user_id', 'LEFT')
            ->field('f.*, u.realname as user_name')
            ->where('f.customer_id', $id)
            ->order('f.follow_time DESC')
            ->select();
        
        $orders = db('orders')
            ->where('customer_id', $id)
            ->order('create_time DESC')
            ->limit(10)
            ->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        foreach ($orders as &$order) {
            $order['status_text'] = isset($statusMap[$order['fulfill_status']]) ? $statusMap[$order['fulfill_status']] : '未知';
        }
        unset($order);
        
        $this->assign('info', $info);
        $this->assign('linkmans', $linkmans);
        $this->assign('follows', $follows);
        $this->assign('orders', $orders);
        
        return $this->fetch();
    }
    
    public function addFollow()
    {
        if ($this->request->isPost()) {
            $data = [
                'customer_id' => input('post.customer_id/d', 0),
                'follow_type' => input('post.follow_type/s', ''),
                'follow_time' => input('post.follow_time/s', '') ?: date('Y-m-d H:i:s'),
                'content' => input('post.content/s', ''),
                'next_time' => input('post.next_time/s', ''),
                'user_id' => $this->adminUserId,
                'create_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['customer_id']) || empty($data['content'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('customer_follow')->insert($data);
            
            $this->writeLog('客户管理', '新增跟进记录，客户ID：' . $data['customer_id'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功']);
        }
    }
    
    public function deleteFollow()
    {
        $id = input('post.id/d', 0);
        
        db('customer_follow')->where('id', $id)->delete();
        
        $this->writeLog('客户管理', '删除跟进记录ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
