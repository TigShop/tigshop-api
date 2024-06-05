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
    // 商户管理
    Route::group('merchant', function () {
        // 列表
        Route::get('list', 'list');
        Route::get('detail', 'detail');
        Route::get('update_field', 'update_field');

    })->prefix('merchant.merchant/');
    // 店铺管理
    Route::group('shop', function () {
        // 列表
        Route::get('list', 'list');
        // 列表
        Route::get('my_shop', 'myShop');

    })->prefix('merchant.shop/');

    Route::group('shop_account', function () {
        // 列表
        Route::get('list', 'list');
        // 列表
        Route::get('log_list', 'logList');

    })->prefix('merchant.shopAccount/');
});
