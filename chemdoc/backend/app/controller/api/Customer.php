<?php
namespace app\controller\api;

use app\BaseController;
use app\service\CustomerService;
use app\service\Result;
use app\validate\CustomerValidate;
use think\App;

class Customer extends BaseController
{
    protected CustomerService $customerService;
    protected CustomerValidate $validate;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->customerService = new CustomerService();
        $this->validate = new CustomerValidate();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->customerService->getList($params));
    }

    public function read($id)
    {
        return json($this->customerService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入客户名称'));
        }

        return json($this->customerService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入客户名称'));
        }

        return json($this->customerService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->customerService->delete((int)$id));
    }
}
