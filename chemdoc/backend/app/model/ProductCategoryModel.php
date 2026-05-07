<?php
declare(strict_types=1);

namespace app\model;

use think\model\Relation\HasMany;

/**
 * 产品分类模型
 */
class ProductCategoryModel extends BaseModel
{
    protected $table = 'product_category';
    protected $primaryKey = 'category_id';

    public function products(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getIsShowTextAttr($value, $data): string
    {
        return (isset($data['is_show']) && $data['is_show'] == 1) ? '显示' : '隐藏';
    }

    public function getLevelTextAttr($value, $data): string
    {
        $level = [1 => '一级', 2 => '二级', 3 => '三级'];
        return $level[$data['level']] ?? '未知';
    }

    public function scopeIsShow($query, bool $isShow = true)
    {
        $query->where('is_show', $isShow ? 1 : 0);
    }

    public function scopeParentId($query, int $parentId)
    {
        $query->where('parent_id', $parentId);
    }
}
