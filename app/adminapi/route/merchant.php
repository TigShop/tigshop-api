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
});
