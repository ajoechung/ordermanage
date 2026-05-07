<?php

namespace app\admin\controller;

class Purchase extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'purchase');
        $this->assign('sub_menu', 'purchase');
        
        $purchaseNo = input('get.purchase_no/s', '');
        $supplierId = input('get.supplier_id/d', 0);
        $fulfillStatus = input('get.fulfill_status/s', '');
        $invoiceStatus = input('get.invoice_status/s', '');
        
        $where = [];
        if ($purchaseNo) {
            $where['p.purchase_no'] = ['like', "%{$purchaseNo}%"];
        }
        if ($supplierId) {
            $where['p.supplier_id'] = $supplierId;
        }
        if ($fulfillStatus !== '') {
            $where['p.fulfill_status'] = $fulfillStatus;
        }
        if ($invoiceStatus !== '') {
            $where['p.invoice_status'] = $invoiceStatus;
        }
        
        $list = db('purchase')
            ->alias('p')
            ->join('supplier s', 's.id = p.supplier_id', 'LEFT')
            ->join('admin_user u', 'u.id = p.user_id', 'LEFT')
            ->join('orders o', 'o.id = p.order_id', 'LEFT')
            ->field('p.*, s.name as supplier_name, u.realname as user_name, o.order_no')
            ->where($where)
            ->order('p.id DESC')
            ->paginate(15);
        
        $suppliers = db('supplier')->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '采购中',
            4 => '已到货',
            5 => '已完成',
            6 => '已取消'
        ];
        
        $invoiceMap = [
            1 => '未开',
            2 => '已申请',
            3 => '已开具',
            4 => '已寄出'
        ];
        
        foreach ($list as &$purchase) {
            $purchase['fulfill_text'] = isset($statusMap[$purchase['fulfill_status']]) ? $statusMap[$purchase['fulfill_status']] : '未知';
            $purchase['invoice_text'] = isset($invoiceMap[$purchase['invoice_status']]) ? $invoiceMap[$purchase['invoice_status']] : '未知';
        }
        unset($purchase);
        
        $this->assign('list', $list);
        $this->assign('suppliers', $suppliers);
        $this->assign('statusMap', $statusMap);
        $this->assign('invoiceMap', $invoiceMap);
        $this->assign('purchaseNo', $purchaseNo);
        $this->assign('supplierId', $supplierId);
        $this->assign('fulfillStatus', $fulfillStatus);
        $this->assign('invoiceStatus', $invoiceStatus);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'purchase_no' => input('post.purchase_no/s', '') ?: $this->generatePurchaseNo(),
                'supplier_id' => input('post.supplier_id/d', 0),
                'amount' => input('post.amount/f', 0),
                'purchase_time' => input('post.purchase_time/s', ''),
                'fulfill_status' => input('post.fulfill_status/d', 1),
                'invoice_status' => input('post.invoice_status/d', 1),
                'invoice_file' => input('post.invoice_file/s', ''),
                'order_id' => input('post.order_id/d', 0) ?: null,
                'remark' => input('post.remark/s', ''),
                'user_id' => $this->adminUserId,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['supplier_id']) || empty($data['amount']) || empty($data['purchase_time'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            $exists = db('purchase')->where('purchase_no', $data['purchase_no'])->find();
            if ($exists) {
                $data['purchase_no'] = $this->generatePurchaseNo();
            }
            
            $purchaseId = db('purchase')->insertGetId($data);
            
            if ($data['order_id']) {
                db('orders')->where('id', $data['order_id'])->update(['purchase_id' => $purchaseId]);
            }
            
            $items = input('post.items/a', []);
            if ($items) {
                foreach ($items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity'])) {
                        $product = db('product')->find($item['product_id']);
                        db('purchase_item')->insert([
                            'purchase_id' => $purchaseId,
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
            
            $this->writeLog('采购单管理', '新增采购单：' . $data['purchase_no'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/purchase/index')]);
        }
        
        $suppliers = db('supplier')->select();
        $products = db('product')->where('status', 1)->select();
        $orders = db('orders')->where('purchase_id', null)->select();
        
        $this->assign('suppliers', $suppliers);
        $this->assign('products', $products);
        $this->assign('orders', $orders);
        $this->assign('purchaseNo', $this->generatePurchaseNo());
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'purchase_no' => input('post.purchase_no/s', ''),
                'supplier_id' => input('post.supplier_id/d', 0),
                'amount' => input('post.amount/f', 0),
                'purchase_time' => input('post.purchase_time/s', ''),
                'fulfill_status' => input('post.fulfill_status/d', 1),
                'invoice_status' => input('post.invoice_status/d', 1),
                'invoice_file' => input('post.invoice_file/s', ''),
                'order_id' => input('post.order_id/d', 0) ?: null,
                'remark' => input('post.remark/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['supplier_id']) || empty($data['amount']) || empty($data['purchase_time'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('purchase')->where('id', $id)->update($data);
            
            if ($data['order_id']) {
                db('orders')->where('id', $data['order_id'])->update(['purchase_id' => $id]);
            }
            
            db('purchase_item')->where('purchase_id', $id)->delete();
            $items = input('post.items/a', []);
            if ($items) {
                foreach ($items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity'])) {
                        $product = db('product')->find($item['product_id']);
                        db('purchase_item')->insert([
                            'purchase_id' => $id,
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
            
            $this->writeLog('采购单管理', '编辑采购单ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/purchase/index')]);
        }
        
        $info = db('purchase')->find($id);
        $items = db('purchase_item')->where('purchase_id', $id)->select();
        $suppliers = db('supplier')->select();
        $products = db('product')->where('status', 1)->select();
        $orders = db('orders')->select();
        
        $this->assign('info', $info);
        $this->assign('items', $items);
        $this->assign('suppliers', $suppliers);
        $this->assign('products', $products);
        $this->assign('orders', $orders);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        db('purchase')->where('id', $id)->delete();
        db('purchase_item')->where('purchase_id', $id)->delete();
        
        $this->writeLog('采购单管理', '删除采购单ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    public function detail()
    {
        $id = input('get.id/d', 0);
        
        $info = db('purchase')
            ->alias('p')
            ->join('supplier s', 's.id = p.supplier_id', 'LEFT')
            ->join('admin_user u', 'u.id = p.user_id', 'LEFT')
            ->join('orders o', 'o.id = p.order_id', 'LEFT')
            ->field('p.*, s.name as supplier_name, s.contact as supplier_contact, s.phone as supplier_phone, u.realname as user_name, o.order_no')
            ->where('p.id', $id)
            ->find();
        
        $items = db('purchase_item')
            ->alias('i')
            ->join('product pr', 'pr.id = i.product_id', 'LEFT')
            ->field('i.*, pr.spec')
            ->where('i.purchase_id', $id)
            ->select();
        
        $statusMap = [
            1 => '待确认',
            2 => '已确认',
            3 => '采购中',
            4 => '已到货',
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
    
    private function generatePurchaseNo()
    {
        return 'PUR' . date('YmdHis') . mt_rand(1000, 9999);
    }
}
