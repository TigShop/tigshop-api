<?php

use think\facade\Route;

// 店铺
Route::group('shop', function () {
    // 店铺
    Route::group('shop', function () {
        // 装修
        Route::get('decorate', 'decorate');
        // 详情
        Route::get('detail', 'detail');
        // 收藏
        Route::post('collect', 'collect')->middleware([
            \app\api\middleware\JWT::class
        ]);

    });
})->prefix("shop.shop/")->middleware([

]);
