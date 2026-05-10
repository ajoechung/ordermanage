<?php
namespace app\controller\api;

use app\BaseController;
use app\service\FollowService;
use app\service\Result;

class Follow extends BaseController
{
    protected FollowService $followService;

    public function __construct()
    {
        parent::__construct();
        $this->followService = new FollowService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->followService->getList($params));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['customer_id'])) {
            return json(Result::validateError('请选择客户'));
        }
        if (empty($data['method'])) {
            return json(Result::validateError('请选择跟进方式'));
        }
        if (empty($data['content'])) {
            return json(Result::validateError('请输入跟进内容'));
        }

        return json($this->followService->create($data));
    }

    public function delete($id)
    {
        return json($this->followService->delete((int)$id));
    }
}
