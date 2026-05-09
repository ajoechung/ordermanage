<?php
namespace app\service;

use app\model\ProductModel;
use app\model\ProductCategoryModel;
use app\model\OperationLogModel;
use think\facade\Db;

class ProductService
{
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $pageSize = (int)($params['page_size'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $categoryId = $params['category_id'] ?? '';
        $status = $params['status'] ?? '';

        $query = ProductModel::with(['category' => function ($q) {
            $q->field('category_id,name');
        }]);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($categoryId)) {
            $query->scope('categoryId', (int)$categoryId);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->order('product_id', 'desc')
            ->page($page, $pageSize)
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            if (isset($item['category'])) {
                $item['category_name'] = $item['category']['name'] ?? '';
                unset($item['category']);
            }
            if (isset($item['attachment']) && is_string($item['attachment'])) {
                $item['attachment'] = json_decode($item['attachment'], true) ?? [];
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getCategories(array $params = []): array
    {
        $parentId = (int)($params['parent_id'] ?? 0);
        $isShow = $params['is_show'] ?? true;

        $query = ProductCategoryModel::where(true);

        if ($isShow) {
            $query->scope('isShow', true);
        }

        $list = $query->where('parent_id', $parentId)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return Result::success($list);
    }

    public function createCategory(array $data): array
    {
        $category = ProductCategoryModel::create([
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? 0,
            'level' => $this->calculateLevel($data['parent_id'] ?? 0),
            'sort' => $data['sort'] ?? 0,
            'is_show' => $data['is_show'] ?? 1,
            'create_user_id' => request()->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '新增分类',
            '新增产品分类：' . $data['name']
        );

        return Result::success(['category_id' => $category->category_id], '分类创建成功');
    }

    public function updateCategory(int $id, array $data): array
    {
        $category = ProductCategoryModel::find($id);
        if (!$category) {
            return Result::notFound('分类不存在');
        }

        $updateData = [];

        $fields = ['name', 'sort', 'is_show'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['parent_id'])) {
            $updateData['parent_id'] = $data['parent_id'];
            $updateData['level'] = $this->calculateLevel($data['parent_id']);
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $category->save($updateData);

        return Result::success(null, '分类更新成功');
    }

    public function deleteCategory(int $id): array
    {
        $category = ProductCategoryModel::find($id);
        if (!$category) {
            return Result::notFound('分类不存在');
        }

        $hasChildren = ProductCategoryModel::where('parent_id', $id)->count();
        if ($hasChildren > 0) {
            return Result::error('该分类存在子分类，无法删除');
        }

        $hasProducts = ProductModel::where('category_id', $id)->whereNull('delete_time')->count();
        if ($hasProducts > 0) {
            return Result::error('该分类存在关联产品，无法删除');
        }

        $category->delete();

        return Result::success(null, '分类删除成功');
    }

    protected function calculateLevel(int $parentId): int
    {
        if ($parentId == 0) {
            return 1;
        }

        $parent = ProductCategoryModel::find($parentId);
        return $parent ? $parent->level + 1 : 1;
    }

    public function getDetail(int $id): array
    {
        $product = ProductModel::with(['category' => function ($q) {
            $q->field('category_id,name');
        }])->find($id);

        if (!$product) {
            return Result::notFound('产品不存在');
        }

        $data = $product->toArray();

        if (isset($data['category'])) {
            $data['category_name'] = $data['category']['name'] ?? '';
            unset($data['category']);
        }

        if (isset($data['attachment']) && is_string($data['attachment'])) {
            $data['attachment'] = json_decode($data['attachment'], true) ?? [];
        }

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $product = ProductModel::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'] ?? null,
            'code' => $data['code'] ?? '',
            'spec' => $data['spec'] ?? '',
            'unit' => $data['unit'] ?? '',
            'price' => $data['price'] ?? 0,
            'description' => $data['description'] ?? '',
            'attachment' => isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null,
            'status' => $data['status'] ?? 1,
            'create_user_id' => request()->user_id ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '新增',
            '新增产品：' . $data['name']
        );

        return Result::success(['product_id' => $product->product_id], '产品创建成功');
    }

    public function update(int $id, array $data): array
    {
        $product = ProductModel::find($id);
        if (!$product) {
            return Result::notFound('产品不存在');
        }

        $updateData = [];

        $fields = ['name', 'category_id', 'code', 'spec', 'unit', 'price', 'description', 'status'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['attachment'])) {
            $updateData['attachment'] = is_array($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : $data['attachment'];
        }

        $updateData['update_time'] = date('Y-m-d H:i:s');

        $product->save($updateData);

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '编辑',
            '编辑产品：' . $product->name
        );

        return Result::success(null, '产品更新成功');
    }

    public function delete(int $id): array
    {
        $product = ProductModel::find($id);
        if (!$product) {
            return Result::notFound('产品不存在');
        }

        $product->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '删除',
            '删除产品：' . $product->name
        );

        return Result::success(null, '产品删除成功');
    }
}
