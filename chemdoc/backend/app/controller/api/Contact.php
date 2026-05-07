<?php
namespace app\controller\api;

use app\BaseController;
use app\service\ContactService;
use app\service\Result;

class Contact extends BaseController
{
    protected ContactService $contactService;

    public function __construct()
    {
        $this->contactService = new ContactService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->contactService->getList($params));
    }

    public function read($id)
    {
        return json($this->contactService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['customer_id'])) {
            return json(Result::validateError('请选择所属客户'));
        }
        if (empty($data['name'])) {
            return json(Result::validateError('请输入联系人姓名'));
        }
        if (empty($data['mobile'])) {
            return json(Result::validateError('请输入手机号'));
        }

        return json($this->contactService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入联系人姓名'));
        }

        return json($this->contactService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->contactService->delete((int)$id));
    }
}
