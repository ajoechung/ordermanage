<?php

// +----------------------------------------------------------------------
// | 基础控制器
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Db;

class Base extends Controller
{
    protected $adminUserId;
    protected $adminUsername;
    protected $adminRealname;
    
    public function _initialize()
    {
        parent::_initialize();
        
        $this->adminUserId = Session::get('admin_user_id');
        $this->adminUsername = Session::get('admin_username');
        $this->adminRealname = Session::get('admin_realname');
        
        if (!$this->adminUserId) {
            $this->redirect('login/index');
        }
        
        $this->assign('adminUserId', $this->adminUserId);
        $this->assign('adminUsername', $this->adminUsername);
        $this->assign('adminRealname', $this->adminRealname);
        
        $this->checkAuth();
    }
    
    protected function checkAuth()
    {
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        
        if ($controller == 'index' && $action == 'index') {
            return true;
        }
        
        $authCode = strtolower($controller . '/' . $action);
        
        if ($this->adminUsername == 'admin') {
            return true;
        }
        
        $userAuths = $this->getUserAuths();
        
        if (!in_array($authCode, $userAuths)) {
            if ($this->request->isAjax()) {
                return json(['code' => 0, 'msg' => '没有权限执行此操作']);
            } else {
                $this->error('没有权限执行此操作');
            }
        }
        
        return true;
    }
    
    protected function getUserAuths()
    {
        $userId = $this->adminUserId;
        
        $cacheKey = 'user_auths_' . $userId;
        $cached = cache($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        $roleIds = Db::name('admin_user_role')
            ->where('user_id', $userId)
            ->column('role_id');
        
        if (empty($roleIds)) {
            return [];
        }
        
        $authIds = Db::name('admin_role_auth')
            ->where('role_id', 'IN', $roleIds)
            ->column('auth_id');
        
        if (empty($authIds)) {
            return [];
        }
        
        $authCodes = Db::name('admin_auth')
            ->where('id', 'IN', $authIds)
            ->column('auth_code');
        
        cache($cacheKey, $authCodes, 3600);
        
        return $authCodes;
    }
    
    protected function writeLog($module, $action, $actionType = '操作')
    {
        Db::name('operation_log')->insert([
            'user_id' => $this->adminUserId,
            'username' => $this->adminUsername,
            'module' => $module,
            'action' => $action,
            'action_type' => $actionType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('user-agent'),
            'request_params' => json_encode(input('post.')),
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    protected function success($msg, $url = null)
    {
        if ($url) {
            $this->redirect($url, ['msg' => $msg]);
        } else {
            return json(['code' => 1, 'msg' => $msg]);
        }
    }
    
    protected function error($msg, $url = null)
    {
        if ($url) {
            $this->redirect($url, ['error' => $msg]);
        } else {
            return json(['code' => 0, 'msg' => $msg]);
        }
    }
}
