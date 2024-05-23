<?php

use think\facade\Route;

// 公共方法
Route::group('common', function () {
    // 配置
    Route::group('config', function () {
        // 基本配置
        Route::get('base', 'common.config/base');
        // 售后服务配置
        Route::get('after_sales_service', 'common.config/afterSalesService');
    });

    // PC
    Route::group('pc', function () {
        // 获取头部导航
        Route::get('get_header', 'common.pc/getHeader');
        // 获取PC导航栏
        Route::get('get_nav', 'common.pc/getNav');
        // 获取PC分类抽屉
        Route::get('get_cat_floor', 'common.pc/getCatFloor');
    });
    // PC
    Route::group('util', function () {
        // 获取头部导航
        Route::get('qr_code', 'common.util/qrCode');
    });
    // 推荐位
    Route::group('recommend', function () {
        // 猜你喜欢
        Route::get('guess_like', 'common.recommend/guessLike');
    });
    // 验证
    Route::group('verification', function () {
        // 获取验证码
        Route::post('captcha', 'common.verification/captcha');
        // 一次验证
        Route::post('check', 'common.verification/check');
        // 二次验证
        Route::post('verification', 'common.verification/verification');
    });
});