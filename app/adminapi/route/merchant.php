<?php

use think\facade\Route;

// 商户
Route::group('merchant', function () {
    // 商户入驻申请
    Route::group('apply', function () {
        // 列表
        Route::get('list', 'merchant.apply/list');
        // 详情
        Route::get('detail', 'merchant.apply/detail');
        // 详情
        Route::get('config', 'merchant.apply/config');
        // 编辑
        Route::post('update', 'merchant.apply/update');
        // 审核
        Route::post('audit', 'merchant.apply/audit');
        // 删除
        Route::post('del', 'merchant.apply/del');
        // 更新字段
        Route::post('update_field', 'merchant.apply/updateField');
        // batch批量操作
        Route::post('batch', 'merchant.apply/batch');
    });
    // 商户账户
    Route::group('account', function () {
        // 列表
        Route::get('list', 'list');
        // 列表
        Route::get('config', 'config');
        // 添加
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // batch批量操作;
        // 详情
        Route::get('detail', 'detail');
    })->prefix('merchant.account/');
    // 商户管理
    Route::group('merchant', function () {
        // 列表
        Route::get('list', 'list');
        Route::get('detail', 'detail');
        Route::post('update_field', 'updateField');

    })->prefix('merchant.merchant/');
    // 店铺管理
    Route::group('shop', function () {
        // 列表
        Route::get('list', 'list');
        // 新增
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 详情
        Route::get('detail', 'detail');
        // 列表
        Route::get('my_shop', 'myShop');
        //更新字段
        Route::post('update_field', 'updateField');

    })->prefix('merchant.shop/');

    Route::group('shop_account', function () {
        // 总览
        Route::get('index', 'index');
        // 列表
        Route::get('list', 'list');
        // 列表
        Route::get('log_list', 'logList');

    })->prefix('merchant.shopAccount/');

    // 分类
    Route::group('shop_product_category', function () {
        // 列表
        Route::get('list', 'list');
        // 添加
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 选择分类
        Route::get('get_all_category', 'getAllCategory');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
        // 详情
        Route::get('detail', 'detail');
    })->prefix('merchant.shopProductCategory/');
    // 店铺提现
    Route::group('shop_withdraw', function () {
        // 列表
        Route::get('list', 'list');
        // 列表
        Route::get('config', 'config');
        // 添加
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // 详情
        Route::get('detail', 'detail');
    })->prefix('merchant.shopWithdraw/');

});
