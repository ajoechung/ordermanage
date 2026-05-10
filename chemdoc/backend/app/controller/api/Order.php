<?php
namespace app\controller\api;

use app\BaseController;
use app\service\OrderService;
use app\service\Result;

class Order extends BaseController
{
    protected OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->orderService->getList($params));
    }

    public function read($id)
    {
        return json($this->orderService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['customer_id'])) {
            return json(Result::validateError('请选择客户'));
        }
        if (empty($data['items'])) {
            return json(Result::validateError('请添加订单产品'));
        }

        return json($this->orderService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();
        return json($this->orderService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->orderService->delete((int)$id));
    }

    public function status()
    {
        $id = $this->request->param('id', 0, 'intval');
        $status = $this->request->param('status', 0, 'intval');

        if ($id == 0) {
            return json(Result::validateError('订单ID不能为空'));
        }
        if ($status == 0) {
            return json(Result::validateError('状态值不能为空'));
        }

        return json($this->orderService->updateStatus($id, $status));
    }
}
