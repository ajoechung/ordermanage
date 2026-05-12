<?php
use think\facade\Route;

Route::group('api', function () {
    Route::get('captcha', 'api.Captcha/index');

    Route::post('login', 'api.Login/index');
    Route::post('logout', 'api.Login/logout');
    Route::get('login/userInfo', 'api.Login/userInfo');

    Route::get('dashboard', 'api.Statistics/dashboard');

    Route::get('customer', 'api.Customer/index');
    Route::get('customer/:id/detail', 'api.Customer/fullDetail');
    Route::get('customer/:id', 'api.Customer/read');
    Route::post('customer', 'api.Customer/save');
    Route::put('customer/:id', 'api.Customer/update');
    Route::delete('customer/:id', 'api.Customer/delete');

    Route::get('contact', 'api.Contact/index');
    Route::get('contact/:id', 'api.Contact/read');
    Route::post('contact', 'api.Contact/save');
    Route::put('contact/:id', 'api.Contact/update');
    Route::delete('contact/:id', 'api.Contact/delete');

    Route::get('follow', 'api.Follow/index');
    Route::post('follow', 'api.Follow/save');
    Route::delete('follow/:id', 'api.Follow/delete');

    Route::get('order', 'api.Order/index');
    Route::get('order/:id', 'api.Order/read');
    Route::post('order', 'api.Order/save');
    Route::put('order/:id', 'api.Order/update');
    Route::delete('order/:id', 'api.Order/delete');
    Route::post('order/status', 'api.Order/status');

    Route::get('supplier', 'api.Supplier/index');
    Route::get('supplier/:id', 'api.Supplier/read');
    Route::post('supplier', 'api.Supplier/save');
    Route::put('supplier/:id', 'api.Supplier/update');
    Route::delete('supplier/:id', 'api.Supplier/delete');

    Route::get('purchase', 'api.Purchase/index');
    Route::get('purchase/:id', 'api.Purchase/read');
    Route::post('purchase', 'api.Purchase/save');
    Route::put('purchase/:id', 'api.Purchase/update');
    Route::delete('purchase/:id', 'api.Purchase/delete');
    Route::post('purchase/status', 'api.Purchase/status');

    Route::get('product', 'api.Product/index');
    Route::get('product/categories', 'api.Product/categories');
    Route::post('product/category', 'api.Product/createCategory');
    Route::put('product/category/:id', 'api.Product/updateCategory');
    Route::delete('product/category/:id', 'api.Product/deleteCategory');
    Route::get('product/:id', 'api.Product/read');
    Route::post('product', 'api.Product/save');
    Route::put('product/:id', 'api.Product/update');
    Route::delete('product/:id', 'api.Product/delete');

    Route::get('statistics/customer', 'api.Statistics/customer');
    Route::get('statistics/order', 'api.Statistics/order');

    Route::get('log', 'api.Log/index');

    Route::get('system/users', 'api.System/users');
    Route::post('system/user', 'api.System/createUser');
    Route::put('system/user/:id', 'api.System/updateUser');
    Route::delete('system/user/:id', 'api.System/deleteUser');
    Route::get('system/groups', 'api.System/groups');
    Route::post('system/group', 'api.System/createGroup');
    Route::put('system/group/:id', 'api.System/updateGroup');
    Route::delete('system/group/:id', 'api.System/deleteGroup');
    Route::get('system/rules', 'api.System/rules');

    Route::post('upload/image', 'api.Upload/image');
    Route::post('upload/file', 'api.Upload/file');
    Route::post('upload/delete', 'api.Upload/delete');

})->middleware(\app\middleware\CorsMiddleware::class);
