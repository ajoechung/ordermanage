<?php

namespace app\admin\controller;

class Category extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'product');
        $this->assign('sub_menu', 'category');
        
        $categories = $this->getCategoryTree();
        $this->assign('categories', $categories);
        
        return $this->fetch();
    }
    
    public function add()
    {
        if ($this->request->isPost()) {
            $data = [
                'name' => input('post.name/s', ''),
                'parent_id' => input('post.parent_id/d', 0),
                'sort' => input('post.sort/d', 0),
                'is_show' => input('post.is_show/d', 1),
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name'])) {
                return json(['code' => 0, 'msg' => '请填写分类名称']);
            }
            
            db('product_category')->insert($data);
            
            $this->writeLog('产品分类', '新增分类：' . $data['name'], '新增');
            
            return json(['code' => 1, 'msg' => '添加成功', 'url' => url('admin/category/index')]);
        }
        
        $categories = $this->getCategoryTree();
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
                'parent_id' => input('post.parent_id/d', 0),
                'sort' => input('post.sort/d', 0),
                'is_show' => input('post.is_show/d', 1),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            if (empty($data['name'])) {
                return json(['code' => 0, 'msg' => '请填写分类名称']);
            }
            
            if ($data['parent_id'] == $id) {
                return json(['code' => 0, 'msg' => '不能将自己设为父级']);
            }
            
            db('product_category')->where('id', $id)->update($data);
            
            $this->writeLog('产品分类', '编辑分类ID：' . $id, '编辑');
            
            return json(['code' => 1, 'msg' => '编辑成功', 'url' => url('admin/category/index')]);
        }
        
        $info = db('product_category')->find($id);
        $categories = $this->getCategoryTree();
        
        $this->assign('info', $info);
        $this->assign('categories', $categories);
        return $this->fetch();
    }
    
    public function delete()
    {
        $id = input('post.id/d', 0);
        
        $childCount = db('product_category')->where('parent_id', $id)->count();
        if ($childCount > 0) {
            return json(['code' => 0, 'msg' => '该分类下有子分类，无法删除']);
        }
        
        $productCount = db('product')->where('category_id', $id)->count();
        if ($productCount > 0) {
            return json(['code' => 0, 'msg' => '该分类下有产品，无法删除']);
        }
        
        db('product_category')->where('id', $id)->delete();
        
        $this->writeLog('产品分类', '删除分类ID：' . $id, '删除');
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
    
    private function getCategoryTree($parentId = 0, $level = 0)
    {
        $categories = db('product_category')
            ->where('parent_id', $parentId)
            ->order('sort ASC')
            ->select();
        
        foreach ($categories as &$cat) {
            $cat['level'] = $level;
            $cat['children'] = $this->getCategoryTree($cat['id'], $level + 1);
        }
        unset($cat);
        
        return $categories;
    }
}
