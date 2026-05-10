<?php
namespace app\controller\api;

use app\BaseController;
use app\service\AuthService;
use app\service\Result;

class Login extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function index()
    {
        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');

        if (empty($username) || empty($password)) {
            return json(Result::validateError('用户名和密码不能为空'));
        }

        return json($this->authService->login($username, $password));
    }

    public function logout()
    {
        return json($this->authService->logout());
    }

    public function userInfo()
    {
        $userId = $this->request->user_id ?? 0;
        if ($userId == 0) {
            return json(Result::unauthorized());
        }
        return json($this->authService->getUserInfo($userId));
    }
}
