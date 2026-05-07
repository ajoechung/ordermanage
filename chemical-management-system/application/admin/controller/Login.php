<?php

// +----------------------------------------------------------------------
// | 登录控制器
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Controller;
use think\Session;
use think\Db;

class Login extends Controller
{
    
    public function index()
    {
        if (Session::get('admin_user_id')) {
            $this->redirect('admin/index/index');
        }
        return $this->fetch();
    }
    
    public function login()
    {
        if ($this->request->isPost()) {
            $username = input('post.username/s', '');
            $password = input('post.password/s', '');
            
            if (empty($username) || empty($password)) {
                return json(['code' => 0, 'msg' => '用户名或密码不能为空']);
            }
            
            $user = Db::name('admin_user')
                ->where('username', $username)
                ->where('status', 1)
                ->find();
            
            if (!$user) {
                return json(['code' => 0, 'msg' => '用户不存在或已被禁用']);
            }
            
            if (md5($password) != $user['password']) {
                return json(['code' => 0, 'msg' => '密码错误']);
            }
            
            Session::set('admin_user_id', $user['id']);
            Session::set('admin_username', $user['username']);
            Session::set('admin_realname', $user['realname']);
            
            Db::name('admin_user')
                ->where('id', $user['id'])
                ->update([
                    'last_login_time' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $this->request->ip(),
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            
            $this->writeLog('登录系统', '登录');
            
            return json(['code' => 1, 'msg' => '登录成功', 'url' => url('admin/index/index')]);
        }
    }
    
    public function logout()
    {
        $this->writeLog('退出系统', '退出');
        Session::delete('admin_user_id');
        Session::delete('admin_username');
        Session::delete('admin_realname');
        $this->redirect('login/index');
    }
    
    private function writeLog($action, $type)
    {
        Db::name('operation_log')->insert([
            'user_id' => Session::get('admin_user_id') ?? 0,
            'username' => Session::get('admin_username') ?? '游客',
            'module' => '登录',
            'action' => $action,
            'action_type' => $type,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->header('user-agent'),
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }
}
