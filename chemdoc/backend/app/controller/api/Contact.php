<?php
namespace app\controller\api;

use app\BaseController;
use app\service\ContactService;
use app\service\Result;
use think\App;
use think\Request;

class Contact extends BaseController
{
    protected ContactService $contactService;
    protected Request $request;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->contactService = new ContactService();
        $this->request = request();
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
        return json($this->contactService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();
        return json($this->contactService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->contactService->delete((int)$id));
    }
}
