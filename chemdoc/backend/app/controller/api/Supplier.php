<?php
namespace app\controller\api;

use app\BaseController;
use app\service\SupplierService;
use app\service\Result;

class Supplier extends BaseController
{
    protected SupplierService $supplierService;

    public function __construct()
    {
        $this->supplierService = new SupplierService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->supplierService->getList($params));
    }

    public function read($id)
    {
        return json($this->supplierService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入供应商名称'));
        }

        return json($this->supplierService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入供应商名称'));
        }

        return json($this->supplierService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->supplierService->delete((int)$id));
    }
}
