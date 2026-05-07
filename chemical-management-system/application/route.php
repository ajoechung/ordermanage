<?php

// +----------------------------------------------------------------------
// | 路由配置
// +----------------------------------------------------------------------
use think\Route;

Route::get('/', 'admin/index/index');
Route::get('login', 'admin/login/index');
Route::post('login', 'admin/login/login');
Route::get('logout', 'admin/login/logout');

Route::group('admin', function () {
    Route::get('/', 'admin/index/index');
    Route::get('index', 'admin/index/index');
    Route::get('dashboard', 'admin/index/dashboard');
    
    // 用户管理
    Route::get('user', 'admin/user/index');
    Route::get('user/index', 'admin/user/index');
    Route::get('user/add', 'admin/user/add');
    Route::post('user/add', 'admin/user/add');
    Route::get('user/edit', 'admin/user/edit');
    Route::post('user/edit', 'admin/user/edit');
    Route::post('user/delete', 'admin/user/delete');
    Route::post('user/resetPwd', 'admin/user/resetPwd');
    
    // 角色管理
    Route::get('role', 'admin/role/index');
    Route::get('role/index', 'admin/role/index');
    Route::get('role/add', 'admin/role/add');
    Route::post('role/add', 'admin/role/add');
    Route::get('role/edit', 'admin/role/edit');
    Route::post('role/edit', 'admin/role/edit');
    Route::post('role/delete', 'admin/role/delete');
    
    // 权限管理
    Route::get('auth', 'admin/auth/index');
    Route::get('auth/index', 'admin/auth/index');
    Route::get('auth/add', 'admin/auth/add');
    Route::post('auth/add', 'admin/auth/add');
    Route::get('auth/edit', 'admin/auth/edit');
    Route::post('auth/edit', 'admin/auth/edit');
    Route::post('auth/delete', 'admin/auth/delete');
    
    // 产品分类
    Route::get('category', 'admin/category/index');
    Route::get('category/index', 'admin/category/index');
    Route::get('category/add', 'admin/category/add');
    Route::post('category/add', 'admin/category/add');
    Route::get('category/edit', 'admin/category/edit');
    Route::post('category/edit', 'admin/category/edit');
    Route::post('category/delete', 'admin/category/delete');
    
    // 产品信息
    Route::get('product', 'admin/product/index');
    Route::get('product/index', 'admin/product/index');
    Route::get('product/add', 'admin/product/add');
    Route::post('product/add', 'admin/product/add');
    Route::get('product/edit', 'admin/product/edit');
    Route::post('product/edit', 'admin/product/edit');
    Route::post('product/delete', 'admin/product/delete');
    
    // 客户管理
    Route::get('customer', 'admin/customer/index');
    Route::get('customer/index', 'admin/customer/index');
    Route::get('customer/add', 'admin/customer/add');
    Route::post('customer/add', 'admin/customer/add');
    Route::get('customer/edit', 'admin/customer/edit');
    Route::post('customer/edit', 'admin/customer/edit');
    Route::post('customer/delete', 'admin/customer/delete');
    Route::get('customer/detail', 'admin/customer/detail');
    
    // 联系人管理
    Route::get('linkman', 'admin/linkman/index');
    Route::get('linkman/index', 'admin/linkman/index');
    Route::get('linkman/add', 'admin/linkman/add');
    Route::post('linkman/add', 'admin/linkman/add');
    Route::get('linkman/edit', 'admin/linkman/edit');
    Route::post('linkman/edit', 'admin/linkman/edit');
    Route::post('linkman/delete', 'admin/linkman/delete');
    
    // 供应商管理
    Route::get('supplier', 'admin/supplier/index');
    Route::get('supplier/index', 'admin/supplier/index');
    Route::get('supplier/add', 'admin/supplier/add');
    Route::post('supplier/add', 'admin/supplier/add');
    Route::get('supplier/edit', 'admin/supplier/edit');
    Route::post('supplier/edit', 'admin/supplier/edit');
    Route::post('supplier/delete', 'admin/supplier/delete');
    
    // 订单管理
    Route::get('order', 'admin/order/index');
    Route::get('order/index', 'admin/order/index');
    Route::get('order/add', 'admin/order/add');
    Route::post('order/add', 'admin/order/add');
    Route::get('order/edit', 'admin/order/edit');
    Route::post('order/edit', 'admin/order/edit');
    Route::post('order/delete', 'admin/order/delete');
    Route::get('order/detail', 'admin/order/detail');
    
    // 采购单管理
    Route::get('purchase', 'admin/purchase/index');
    Route::get('purchase/index', 'admin/purchase/index');
    Route::get('purchase/add', 'admin/purchase/add');
    Route::post('purchase/add', 'admin/purchase/add');
    Route::get('purchase/edit', 'admin/purchase/edit');
    Route::post('purchase/edit', 'admin/purchase/edit');
    Route::post('purchase/delete', 'admin/purchase/delete');
    Route::get('purchase/detail', 'admin/purchase/detail');
    
    // 数据统计
    Route::get('statistics', 'admin/statistics/index');
    Route::get('statistics/index', 'admin/statistics/index');
    Route::get('statistics/customer', 'admin/statistics/customer');
    Route::get('statistics/order', 'admin/statistics/order');
    
    // 操作日志
    Route::get('log', 'admin/log/index');
    Route::get('log/index', 'admin/log/index');
}, ['prefix' => 'app\\admin\\controller\\']);
