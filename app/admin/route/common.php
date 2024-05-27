<?php

use think\facade\Route;

// 公共方法组
Route::group('common', function () {
    Route::group('cache_manage', function () {
        // 清除缓存
        Route::post('cleanup', 'common.cacheManage/cleanup');
    });

    Route::group('verification', function () {
        Route::get('captcha', 'common.verification/captcha');
        // 一次验证
        Route::post('check', 'common.verification/check');
    });
});