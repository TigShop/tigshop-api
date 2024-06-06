<?php
use think\facade\Route;

// 购物车
Route::group('cart', function () {
    // 购物车
    Route::group('cart', function () {
        // 购物车列表
        Route::get('list', 'cart.cart/list');
        // 获取购物车商品数量
        Route::get('get_count', 'cart.cart/getCount');
        // 更新购物车商品选择状态
        Route::post('update_check', 'cart.cart/updateCheck');
        // 更新购物车商品数量
        Route::post('update_item', 'cart.cart/updateItem');
        // 删除购物车商品
        Route::post('remove_item', 'cart.cart/removeItem');
        // 清空购物车
        Route::post('clear', 'cart.cart/clear');
        // 购物车列表
        Route::get('get_coupon_discount', 'cart.cart/getCouponDiscount');
    });
})->middleware([
    \app\api\middleware\JWT::class,
]);
