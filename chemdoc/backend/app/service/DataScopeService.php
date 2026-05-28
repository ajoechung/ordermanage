<?php
namespace app\service;

use think\facade\Db;

class DataScopeService
{
    /**
     * 判断当前用户是否可以查看全部数据
     */
    public static function canViewAllData(): bool
    {
        $userId = request()->user_id ?? 0;
        
        // 超级管理员可以看全部
        if ($userId === 1) {
            return true;
        }
        
        // 检查用户是否有查看全部数据的权限
        return self::hasViewAllDataPermission($userId);
    }
    
    /**
     * 获取当前用户可访问的客户ID列表
     * 如果可以看全部，返回空数组
     */
    public static function getAccessibleCustomerIds(): array
    {
        if (self::canViewAllData()) {
            return [];
        }
        
        $userId = request()->user_id ?? 0;
        
        if (empty($userId)) {
            return [];
        }
        
        try {
            return Db::name('customer')
                ->where('owner_user_id', $userId)
                ->whereNull('delete_time')
                ->column('customer_id');
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * 获取当前用户可访问的供应商ID列表
     * 如果可以看全部，返回空数组
     */
    public static function getAccessibleSupplierIds(): array
    {
        if (self::canViewAllData()) {
            return [];
        }
        
        $userId = request()->user_id ?? 0;
        
        return Db::name('supplier')
            ->where('owner_user_id', $userId)
            ->whereNull('delete_time')
            ->column('supplier_id');
    }
    
    /**
     * 应用客户数据范围限制到查询
     */
    public static function applyCustomerScope($query, string $customerIdField = 'customer_id')
    {
        // 调试信息 - 通过静态变量存储用于返回
        static $debugInfo = [];
        
        $userId = request()->user_id ?? 0;
        $username = request()->username ?? '';
        
        $debugInfo['userId'] = $userId;
        $debugInfo['username'] = $username;
        $debugInfo['appliedAt'] = date('Y-m-d H:i:s');
        
        // 如果是管理员，可以查看全部数据，不需要过滤
        if (self::canViewAllData()) {
            $debugInfo['isAdmin'] = true;
            $debugInfo['message'] = '管理员身份，返回全部数据';
            return $query;
        }
        
        $debugInfo['isAdmin'] = false;
        
        $customerIds = self::getAccessibleCustomerIds();
        
        $debugInfo['customerIdsCount'] = count($customerIds);
        
        if (!empty($customerIds)) {
            $debugInfo['message'] = '应用了数据过滤，仅显示' . count($customerIds) . '个负责的客户';
            $query->whereIn($customerIdField, $customerIds);
        } else {
            $debugInfo['message'] = '用户不是管理员且没有负责任何客户，返回空结果';
            // 如果用户不是管理员且没有负责任何客户，返回空结果
            $query->where($customerIdField, 0);
        }
        
        // 将调试信息存储到全局变量
        $GLOBALS['dataScopeDebug'] = $debugInfo;
        
        return $query;
    }
    
    /**
     * 获取调试信息
     */
    public static function getDebugInfo()
    {
        return $GLOBALS['dataScopeDebug'] ?? ['message' => '未调用数据权限过滤'];
    }
    
    /**
     * 应用供应商数据范围限制到查询
     */
    public static function applySupplierScope($query, string $supplierIdField = 'supplier_id')
    {
        // 如果是管理员，可以查看全部数据，不需要过滤
        if (self::canViewAllData()) {
            return $query;
        }
        
        $supplierIds = self::getAccessibleSupplierIds();
        
        if (!empty($supplierIds)) {
            $query->whereIn($supplierIdField, $supplierIds);
        } else {
            // 如果用户不是管理员且没有负责任何供应商，返回空结果
            $query->where($supplierIdField, 0);
        }
        
        return $query;
    }
    
    /**
     * 检查用户是否有查看全部数据的权限
     */
    protected static function hasViewAllDataPermission(int $userId): bool
    {
        // 获取用户的角色
        $roleIds = Db::name('auth_group_access')
            ->where('uid', $userId)
            ->column('group_id');
        
        if (empty($roleIds)) {
            return false;
        }
        
        // 检查这些角色是否有查看全部数据的权限
        // 特定角色ID可以看全部（1=超级管理员，2=经理）
        foreach ($roleIds as $roleId) {
            if (in_array($roleId, [1, 2])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查是否有权限访问某个客户
     */
    public static function canAccessCustomer(int $customerId): bool
    {
        if (self::canViewAllData()) {
            return true;
        }
        
        $customerIds = self::getAccessibleCustomerIds();
        return in_array($customerId, $customerIds);
    }
    
    /**
     * 检查是否有权限访问某个供应商
     */
    public static function canAccessSupplier(int $supplierId): bool
    {
        if (self::canViewAllData()) {
            return true;
        }
        
        $supplierIds = self::getAccessibleSupplierIds();
        return in_array($supplierId, $supplierIds);
    }
}
