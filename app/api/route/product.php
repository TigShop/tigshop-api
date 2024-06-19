<?php

use think\facade\Route;

Route::group('product', function () {

    //兑换
    Route::group('exchange', function () {
        // 列表
        Route::get('list', 'product.exchange/list');
        // 详情
        Route::get('detail', 'product.exchange/detail');

    });

    // 商品
    Route::group('product', function () {
        // 详情
        Route::get('detail', 'product.product/detail');
        // 评论
        Route::get('get_comment', 'product.product/getComment');
        // 评论列表
        Route::get('get_comment_list', 'product.product/getCommentList');
        // 咨询列表
        Route::get('get_feedback_list', 'product.product/getFeedbackList');
        // 可用信息sku和活动等
        Route::get('get_product_availability', 'product.product/getProductAvailability');
        // 列表
        Route::get('list', 'product.product/list');
        // 优惠卷
        Route::get('get_coupon', 'product.product/getCouponList');
        // 是否收藏
        Route::get('is_collect', 'product.product/isCollect');
        //加入购物车
        Route::post('add_to_cart', 'product.product/addToCart')->middleware([
            \app\api\middleware\CheckLogin::class,
        ]);

    });
});
