<?php
namespace app\controller\api;

use app\BaseController;
use app\service\AuthService;
use app\service\Result;
use think\App;

class Login extends BaseController
{
    protected AuthService $authService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->authService = new AuthService();
    }

    public function index()
    {
        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');
        $captcha = $this->request->param('captcha', '');
        $captchaKey = $this->request->param('captcha_key', '');

        if (empty($username) || empty($password)) {
            return json(Result::validateError('用户名和密码不能为空'));
        }

        if (empty($captcha) || empty($captchaKey)) {
            return json(Result::validateError('验证码不能为空'));
        }

        $cachedCaptcha = cache('captcha_' . $captchaKey);
        if (empty($cachedCaptcha)) {
            return json(Result::validateError('验证码已过期，请刷新'));
        }

        if (strtolower($captcha) !== $cachedCaptcha) {
            cache('captcha_' . $captchaKey, null);
            return json(Result::validateError('验证码错误'));
        }

        cache('captcha_' . $captchaKey, null);

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
