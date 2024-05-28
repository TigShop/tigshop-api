<?php

use think\facade\Route;

// 首页
Route::group('merchant', function () {
    // 首页
    Route::group('merchant', function () {
        // 申请入驻
        Route::post('apply', 'merchant.merchant/apply');

    });
});
