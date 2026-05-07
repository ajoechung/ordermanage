<?php
namespace app\controller\api;

use app\BaseController;
use app\service\PurchaseService;
use app\service\Result;

class Purchase extends BaseController
{
    protected PurchaseService $purchaseService;

    public function __construct()
    {
        $this->purchaseService = new PurchaseService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->purchaseService->getList($params));
    }

    public function read($id)
    {
        return json($this->purchaseService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['supplier_id'])) {
            return json(Result::validateError('请选择供应商'));
        }

        return json($this->purchaseService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();
        return json($this->purchaseService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->purchaseService->delete((int)$id));
    }

    public function status()
    {
        $id = $this->request->param('id', 0, 'intval');
        $status = $this->request->param('status', 0, 'intval');

        if ($id == 0) {
            return json(Result::validateError('采购单ID不能为空'));
        }
        if ($status == 0) {
            return json(Result::validateError('状态值不能为空'));
        }

        return json($this->purchaseService->updateStatus($id, $status));
    }
}
