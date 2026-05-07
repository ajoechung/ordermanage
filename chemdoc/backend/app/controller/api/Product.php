<?php
namespace app\controller\api;

use app\BaseController;
use app\service\ProductService;
use app\service\Result;

class Product extends BaseController
{
    protected ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
    }

    public function index()
    {
        $params = $this->request->param();
        return json($this->productService->getList($params));
    }

    public function read($id)
    {
        return json($this->productService->getDetail((int)$id));
    }

    public function save()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入产品名称'));
        }

        return json($this->productService->create($data));
    }

    public function update($id)
    {
        $data = $this->request->put();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入产品名称'));
        }

        return json($this->productService->update((int)$id, $data));
    }

    public function delete($id)
    {
        return json($this->productService->delete((int)$id));
    }

    public function categories()
    {
        $params = $this->request->param();
        return json($this->productService->getCategories($params));
    }

    public function createCategory()
    {
        $data = $this->request->post();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入分类名称'));
        }

        return json($this->productService->createCategory($data));
    }

    public function updateCategory($id)
    {
        $data = $this->request->put();

        if (empty($data['name'])) {
            return json(Result::validateError('请输入分类名称'));
        }

        return json($this->productService->updateCategory((int)$id, $data));
    }

    public function deleteCategory($id)
    {
        return json($this->productService->deleteCategory((int)$id));
    }
}
