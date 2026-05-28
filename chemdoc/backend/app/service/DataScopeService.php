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
        $userId = request()->user_id ?? 0;
        $username = request()->username ?? '';
        
        // 记录日志到文件
        $logFile = __DIR__ . '/../runtime/logs/data_scope.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        $log = date('Y-m-d H:i:s') . " | applyCustomerScope | userId: {$userId}, username: {$username}\n";
        
        // 如果是管理员，可以查看全部数据，不需要过滤
        if (self::canViewAllData()) {
            $log .= date('Y-m-d H:i:s') . " | applyCustomerScope | User is admin, returning all data\n";
            file_put_contents($logFile, $log, FILE_APPEND);
            return $query;
        }
        
        $customerIds = self::getAccessibleCustomerIds();
        
        $log .= date('Y-m-d H:i:s') . " | applyCustomerScope | customerIds count: " . count($customerIds) . "\n";
        
        if (!empty($customerIds)) {
            $log .= date('Y-m-d H:i:s') . " | applyCustomerScope | Applying whereIn filter with customerIds\n";
            $query->whereIn($customerIdField, $customerIds);
        } else {
            $log .= date('Y-m-d H:i:s') . " | applyCustomerScope | No customerIds, applying where(" . $customerIdField . ", 0)\n";
            // 如果用户不是管理员且没有负责任何客户，返回空结果
            $query->where($customerIdField, 0);
        }
        
        file_put_contents($logFile, $log, FILE_APPEND);
        
        return $query;
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
