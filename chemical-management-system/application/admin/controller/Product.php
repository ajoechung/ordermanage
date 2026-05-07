<?php

namespace app\admin\controller;

class Product extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'product');
        $this->assign('sub_menu', 'product');
        
        $name = input('get.name/s', '');
        $categoryId = input('get.category_id/d', 0);
        $status = input('get.status/s', '');
        
        $where = [];
        if ($name) {
            $where['p.name'] = ['like', "%{$name}%"];
        }
        if ($categoryId) {
            $where['p.category_id'] = $categoryId;
        }
        if ($status !== '') {
            $where['p.status'] = $status;
        }
        
        $list = db('product')
            ->alias('p')
            ->join('product_category c', 'c.id = p.category_id', 'LEFT')
            ->field('p.*, c.name as category_name')
            ->where($where)
            ->order('p.id DESC')
            ->paginate(15);
        
        $categories = $this->getCategoryOptions();
        
        $this->assign('list', $list);
        $this->assign('categories', $categories);
        $this->assign('name', $name);
        $this->assign('categoryId', $categoryId);
        $this->assign('status', $status);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'name' => input('post.name/s', ''),
                'category_id' => input('post.category_id/d', 0),
                'description' => input('post.description/s', ''),
                'spec' => input('post.spec/s', ''),
                'price' => input('post.price/f', 0),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name']) || empty($data['category_id'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('product')->insert($data);
            
            $this->writeLog('产品管理', '新增产品：' . $data['name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/product/index')]);
        }
        
        $categories = $this->getCategoryOptions();
        $this->assign('categories', $categories);
        return $this->fetch();
    }
    
    public function edit()
    {
        $id = input('get.id/d', 0);
        
        if ($this->request->isPost()) {
            $id = input('post.id/d', 0);
            $data = [
                'name' => input('post.name/s', ''),
                'category_id' => input('post.category_id/d', 0),
                'description' => input('post.description/s', ''),
                'spec' => input('post.spec/s', ''),
                'price' => input('post.price/f', 0),
                'status' => input('post.status/d', 1),
                'attachment' => input('post.attachment/s', ''),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name']) || empty($data['category_id'])) {
                return json(['code' => 0, 'msg' => '请填写完整信息']);
            }
            
            db('product')->where('id', $id)->update($data);
            
            $this->writeLog('产品管理', '编辑产品ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/product/index')]);
        }
        
        $info = db('product')->find($id);
        $categories = $this->getCategoryOptions();
        
        $this->assign('info', $info);
        $this->assign('categories', $categories);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        db('product')->where('id', $id)->delete();
        
        $this->writeLog('产品管理', '删除产品ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    private function getCategoryOptions($parentId = 0, $level = 0, $selectedId = 0)
    {
        $categories = db('product_category')
            ->where('parent_id', $parentId)
            ->where('is_show', 1)
            ->order('sort ASC')
            ->select();
        
        $options = [];
        foreach ($categories as $cat) {
            $prefix = str_repeat('├─ ', $level);
            $cat['full_name'] = $prefix . $cat['name'];
            $cat['level'] = $level;
            $options[] = $cat;
            
            $children = $this->getCategoryOptions($cat['id'], $level + 1, $selectedId);
            $options = array_merge($options, $children);
        }
        
        return $options;
    }
}
