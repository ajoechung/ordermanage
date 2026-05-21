<?php
namespace app\controller\api;

use app\BaseController;
use app\service\DictService;
use think\App;

class Dict extends BaseController
{
    protected DictService $dictService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->dictService = new DictService();
    }

    public function typeList()
    {
        $params = $this->request->param();
        return json($this->dictService->getTypeList($params));
    }

    public function typeDetail($id)
    {
        return json($this->dictService->getTypeDetail((int)$id));
    }

    public function createType()
    {
        $data = $this->request->post();
        return json($this->dictService->createType($data));
    }

    public function updateType($id)
    {
        $data = $this->request->put();
        return json($this->dictService->updateType((int)$id, $data));
    }

    public function deleteType($id)
    {
        return json($this->dictService->deleteType((int)$id));
    }

    public function dataList()
    {
        $params = $this->request->param();
        return json($this->dictService->getDataList($params));
    }

    public function dataDetail($id)
    {
        return json($this->dictService->getDataDetail((int)$id));
    }

    public function createData()
    {
        $data = $this->request->post();
        return json($this->dictService->createData($data));
    }

    public function updateData($id)
    {
        $data = $this->request->put();
        return json($this->dictService->updateData((int)$id, $data));
    }

    public function deleteData($id)
    {
        return json($this->dictService->deleteData((int)$id));
    }

    public function getByCode($code)
    {
        return json($this->dictService->getDictByCode($code));
    }
}
