<?php

use think\facade\Route;

// 店铺
Route::group('shop', function () {
    // 店铺
    Route::group('shop', function () {
        // 购物车列表
        Route::get('decorate', 'decorate');
        // 详情
        Route::get('detail', 'detail');
    });
})->prefix("shop.shop/")->middleware([

]);
