<?php
namespace app\controller\api;

use app\BaseController;
use app\service\AuthService;
use app\service\Result;
use think\App;
use think\Request;

class Login extends BaseController
{
    protected AuthService $authService;
    protected Request $request;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->authService = new AuthService();
        $this->request = request();
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
