<?php
namespace app\controller\api;

use app\BaseController;
use app\service\CustomerService;
use app\service\Result;
use app\validate\CustomerValidate;

class Customer extends BaseController
{
    protected CustomerService $customerService;
    protected CustomerValidate $validate;

    public function __construct()
    {
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

        if (!$this->validate->scene('create')->check($data)) {
            return json(Result::validateError($this->validate->getError()));
        }

        return json($this->customerService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();

        if (!$this->validate->scene('update')->check($data)) {
            return json(Result::validateError($this->validate->getError()));
        }

        return json($this->customerService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->customerService->delete((int)$id));
    }
}
