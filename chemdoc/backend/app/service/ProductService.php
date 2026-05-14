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
        $categoryIds = $params['category_ids'] ?? [];
        $status = $params['status'] ?? '';

        $query = ProductModel::with(['category']);

        if (!empty($keyword)) {
            $query->scope('keyword', $keyword);
        }

        if (!empty($categoryId)) {
            $query->scope('categoryId', (int)$categoryId);
        } elseif (!empty($categoryIds)) {
            if (is_string($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }
            $categoryIds = array_filter($categoryIds, function($id) {
                return !empty($id);
            });
            if (!empty($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            }
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
            foreach (['attachment', 'msds', 'coa'] as $field) {
                if (isset($item[$field]) && is_string($item[$field])) {
                    $item[$field] = json_decode($item[$field], true) ?? [];
                }
            }
        }

        return Result::paginate($total, $list, $page, $pageSize);
    }

    public function getCategories(array $params = []): array
    {
        $tree = $params['tree'] ?? false;
        $isShow = $params['is_show'] ?? true;

        $query = ProductCategoryModel::where(true);

        if ($isShow === true || $isShow === 'true') {
            $query->where('is_show', 1);
        }

        $list = $query->order('sort', 'asc')
            ->select()
            ->toArray();

        if ($tree) {
            $list = $this->buildCategoryTree($list);
        }

        return Result::success($list);
    }

    protected function buildCategoryTree(array $categories, int $parentId = 0): array
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildCategoryTree($categories, $category['category_id']);
                if (!empty($children)) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        return $tree;
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

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '编辑分类',
            '编辑产品分类：' . $category->name
        );

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

        try {
            $hasProducts = ProductModel::where('category_id', $id)->count();
            if ($hasProducts > 0) {
                return Result::error('该分类存在关联产品，无法删除');
            }
        } catch (\Exception $e) {
            // 如果检查失败，继续删除
        }

        $category->delete();

        OperationLogModel::log(
            request()->user_id ?? 0,
            request()->username ?? '',
            '产品管理',
            '删除分类',
            '删除产品分类：' . $category->name
        );

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
        $product = ProductModel::with(['category'])->find($id);

        if (!$product) {
            return Result::notFound('产品不存在');
        }

        $data = $product->toArray();

        if (isset($data['category'])) {
            $data['category_name'] = $data['category']['name'] ?? '';
            unset($data['category']);
        }

        foreach (['attachment', 'msds', 'coa'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = json_decode($data[$field], true) ?? [];
            }
        }

        return Result::success($data);
    }

    public function create(array $data): array
    {
        $status = $data['status'] ?? 1;
        if (is_string($status)) {
            $status = $status === '启用' ? 1 : 0;
        }
        
        $product = ProductModel::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'] ?? null,
            'code' => !empty($data['code']) ? $data['code'] : null,
            'spec' => $data['spec'] ?? '',
            'unit' => $data['unit'] ?? '',
            'description' => $data['description'] ?? '',
            'origin' => $data['origin'] ?? '',
            'attachment' => isset($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : null,
            'msds' => isset($data['msds']) ? json_encode($data['msds'], JSON_UNESCAPED_UNICODE) : null,
            'coa' => isset($data['coa']) ? json_encode($data['coa'], JSON_UNESCAPED_UNICODE) : null,
            'status' => $status,
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

        $fields = ['name', 'category_id', 'spec', 'unit', 'description', 'origin'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['code'])) {
            $updateData['code'] = !empty($data['code']) ? $data['code'] : null;
        }

        if (isset($data['status'])) {
            $status = $data['status'];
            if (is_string($status)) {
                $status = $status === '启用' ? 1 : 0;
            }
            $updateData['status'] = $status;
        }

        if (isset($data['attachment'])) {
            $updateData['attachment'] = is_array($data['attachment']) ? json_encode($data['attachment'], JSON_UNESCAPED_UNICODE) : $data['attachment'];
        }

        if (isset($data['msds'])) {
            $updateData['msds'] = is_array($data['msds']) ? json_encode($data['msds'], JSON_UNESCAPED_UNICODE) : $data['msds'];
        }

        if (isset($data['coa'])) {
            $updateData['coa'] = is_array($data['coa']) ? json_encode($data['coa'], JSON_UNESCAPED_UNICODE) : $data['coa'];
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
