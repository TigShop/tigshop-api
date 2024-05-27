<?php
use think\facade\Route;

// 首页
Route::group('home', function () {
    // 首页
    Route::group('home', function () {
        // 首页
        Route::get('api', 'home.home/api');
        // PC首页
        Route::get('pc_index', 'home.home/pcIndex');
        // 首页今日推荐
        Route::get('get_recommend', 'home.home/getRecommend');
        // 首页秒杀
        Route::get('get_seckill', 'home.home/getSeckill');
        // 首页优惠券
        Route::get('get_coupon', 'home.home/getCoupon');
        // 首页分类栏
        Route::get('mobile_cat_nav', 'home.home/mobileCatNav');
        // 移动端导航栏
        Route::get('mobile_nav', 'home.home/mobileNav');
        // 客服
        Route::get('kefu', 'home.home/kefu');
        // 友情链接
        Route::get('friend_links', 'home.home/friendLinks');
    });
});
