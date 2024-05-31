<?php

use think\facade\Route;

// 首页
Route::group('merchant', function () {
    // 首页
    Route::group('merchant', function () {
        // 申请入驻
        Route::post('apply', 'merchant.merchant/apply');
        //我的申请
        Route::get('my_apply', 'merchant.merchant/myApply');
        //申请详情
        Route::get('apply_detail', 'merchant.merchant/applyDetail');
    });
});
