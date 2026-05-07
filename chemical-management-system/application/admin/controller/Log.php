<?php

namespace app\admin\controller;

class Log extends Base
{
    
    public function index()
    {
        $this->assign('menu', 'log');
        $this->assign('sub_menu', 'log');
        
        $username = input('get.username/s', '');
        $module = input('get.module/s', '');
        $actionType = input('get.action_type/s', '');
        
        $where = [];
        if ($username) {
            $where['username'] = ['like', "%{$username}%"];
        }
        if ($module) {
            $where['module'] = ['like', "%{$module}%"];
        }
        if ($actionType) {
            $where['action_type'] = $actionType;
        }
        
        if ($this->adminUsername != 'admin') {
            $where['user_id'] = $this->adminUserId;
        }
        
        $list = db('operation_log')
            ->where($where)
            ->order('id DESC')
            ->paginate(15);
        
        $this->assign('list', $list);
        $this->assign('username', $username);
        $this->assign('module', $module);
        $this->assign('actionType', $actionType);
        
        return $this->fetch();
    }
}
