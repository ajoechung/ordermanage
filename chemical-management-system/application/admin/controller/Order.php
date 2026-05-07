<?php

namespace app\admin\controller;

class Order extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'order');
        $this->assign('sub_menu', 'order');
        
        $orderNo = input('get.order_no/s', '');
        $customerId = input('get.customer_id/d', 0);
        $fulfillStatus = input('get.fulfill_status/s', '');
        $invoiceStatus = input('get.invoice_status/s', '');
        
        $where = [];
        if ($orderNo) {
            $where['o.order_no'] = ['like', "%{$orderNo}%"];
        }
        if ($customerId) {
            $where['o.customer_id'] = $customerId;
        }
        if ($fulfillStatus !== '') {
            $where['o.fulfill_status'] = $fulfillStatus;
        }
        if ($invoiceStatus !== '') {
            $where['o.invoice_status'] = $invoiceStatus;
        }
        
        $list = db('orders')
            ->alias('o')
            ->join('customer c', 'c.id = o.customer_id', 'LEFT')
            ->join('admin_user u', 'u.id = o.user_id', 'LEFT')
            ->join('purchase p', 'p.id = o.purchase_id', 'LEFT')
            ->field('o.*, c.customer_name, u.realname as user_name, p.purchase_no')
            ->where($where)
            ->order('o.id DESC')
            ->paginate(15);
        
        $customers = db('customer')->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        $invoiceMap = [
            1 => '未开',
            2 => '已申请',
            3 => '已开具',
            4 => '已寄出'
        ];
        
        foreach ($list as &$order) {
            $order['fulfill_text'] = isset($statusMap[$order['fulfill_status']]) ? $statusMap[$order['fulfill_status']] : '未知';
            $order['invoice_text'] = isset($invoiceMap[$order['invoice_status']]) ? $invoiceMap[$order['invoice_status']] : '未知';
        }
        unset($order);
        
        $this->assign('list', $list);
        $this->assign('customers', $customers);
        $this->assign('statusMap', $statusMap);
        $this->assign('invoiceMap', $invoiceMap);
        $this->assign('orderNo', $orderNo);
        $this->assign('customerId', $customerId);
        $this->assign('fulfillStatus', $fulfillStatus);
        $this->assign('invoiceStatus', $invoiceStatus);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'order_no' => input('post.order_no/s', '') ?: $this->generateOrderNo(),
                'customer_id' => input('post.customer_id/d', 0),
                'amount' => input('post.amount/f', 0),
                'order_time' => input('post.order_time/s', ''),
                'fulfill_status' => input('post.fulfill_status/d', 1),
                'invoice_status' => input('post.invoice_status/d', 1),
                'invoice_file' => input('post.invoice_file/s', ''),
                'purchase_id' => input('post.purchase_id/d', 0) ?: null,
                'remark' => input('post.remark/s', ''),
                'user_id' => $this->adminUserId,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['customer_id']) || empty($data['amount']) || empty($data['order_time'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            $exists = db('orders')->where('order_no', $data['order_no'])->find();
            if ($exists) {
                $data['order_no'] = $this->generateOrderNo();
            }
            
            $orderId = db('orders')->insertGetId($data);
            
            $items = input('post.items/a', []);
            if ($items) {
                foreach ($items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity'])) {
                        $product = db('product')->find($item['product_id']);
                        db('order_item')->insert([
                            'order_id' => $orderId,
                            'product_id' => $item['product_id'],
                            'product_name' => $product ? $product['name'] : '',
                            'quantity' => $item['quantity'],
                            'price' => $item['price'] ?: ($product ? $product['price'] : 0),
                            'amount' => ($item['quantity'] * ($item['price'] ?: ($product ? $product['price'] : 0))),
                            'remark' => isset($item['remark']) ? $item['remark'] : '',
                            'create_time' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            $this->writeLog('订单管理', '新增订单：' . $data['order_no'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/order/index')]);
        }
        
        $customers = db('customer')->select();
        $products = db('product')->where('status', 1)->select();
        
        $this->assign('customers', $customers);
        $this->assign('products', $products);
        $this->assign('orderNo', $this->generateOrderNo());
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'order_no' => input('post.order_no/s', ''),
                'customer_id' => input('post.customer_id/d', 0),
                'amount' => input('post.amount/f', 0),
                'order_time' => input('post.order_time/s', ''),
                'fulfill_status' => input('post.fulfill_status/d', 1),
                'invoice_status' => input('post.invoice_status/d', 1),
                'invoice_file' => input('post.invoice_file/s', ''),
                'purchase_id' => input('post.purchase_id/d', 0) ?: null,
                'remark' => input('post.remark/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['customer_id']) || empty($data['amount']) || empty($data['order_time'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('orders')->where('id', $id)->update($data);
            
            db('order_item')->where('order_id', $id)->delete();
            $items = input('post.items/a', []);
            if ($items) {
                foreach ($items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity'])) {
                        $product = db('product')->find($item['product_id']);
                        db('order_item')->insert([
                            'order_id' => $id,
                            'product_id' => $item['product_id'],
                            'product_name' => $product ? $product['name'] : '',
                            'quantity' => $item['quantity'],
                            'price' => $item['price'] ?: ($product ? $product['price'] : 0),
                            'amount' => ($item['quantity'] * ($item['price'] ?: ($product ? $product['price'] : 0))),
                            'remark' => isset($item['remark']) ? $item['remark'] : '',
                            'create_time' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            $this->writeLog('订单管理', '编辑订单ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/order/index')]);
        }
        
        $info = db('orders')->find($id);
        $items = db('order_item')->where('order_id', $id)->select();
        $customers = db('customer')->select();
        $products = db('product')->where('status', 1)->select();
        $purchases = db('purchase')->select();
        
        $this->assign('info', $info);
        $this->assign('items', $items);
        $this->assign('customers', $customers);
        $this->assign('products', $products);
        $this->assign('purchases', $purchases);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        db('orders')->where('id', $id)->delete();
        db('order_item')->where('order_id', $id)->delete();
        
        $this->writeLog('订单管理', '删除订单ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    public function detail()
    {
        $id = input('get.id/d', 0);
        
        $info = db('orders')
            ->alias('o')
            ->join('customer c', 'c.id = o.customer_id', 'LEFT')
            ->join('admin_user u', 'u.id = o.user_id', 'LEFT')
            ->join('purchase p', 'p.id = o.purchase_id', 'LEFT')
            ->field('o.*, c.customer_name, c.phone as customer_phone, u.realname as user_name, p.purchase_no')
            ->where('o.id', $id)
            ->find();
        
        $items = db('order_item')
            ->alias('i')
            ->join('product p', 'p.id = i.product_id', 'LEFT')
            ->field('i.*, p.spec, p.price as product_price')
            ->where('i.order_id', $id)
            ->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '生产中',
            4 => '已发货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        $invoiceMap = [
            1 => '未开',
            2 => '已申请',
            3 => '已开具',
            4 => '已寄出'
        ];
        
        $info['fulfill_text'] = isset($statusMap[$info['fulfill_status']]) ? $statusMap[$info['fulfill_status']] : '未知';
        $info['invoice_text'] = isset($invoiceMap[$info['invoice_status']]) ? $invoiceMap[$info['invoice_status']] : '未知';
        
        $this->assign('info', $info);
        $this->assign('items', $items);
        
        return $this->fetch();
    }
    
    private function generateOrderNo()
    {
        return 'ORD' . date('YmdHis') . mt_rand(1000, 9999);
    }
}
