<?php
use think\facade\Route;

// 营销组
Route::group('promotion', function () {
    // 优惠券管理
    Route::group('coupon', function () {
        // 优惠券列表
        Route::get('list', 'promotion.coupon/list');
        // 优惠券配置
        Route::get('config', 'promotion.coupon/config');
        // 优惠券详情
        Route::get('detail', 'promotion.coupon/detail');
        // 优惠券添加
        Route::post('create', 'promotion.coupon/create');
        // 优惠券编辑
        Route::post('update', 'promotion.coupon/update');
        // 更新字段
        Route::post('update_field', 'promotion.coupon/updateField');
        // 优惠券删除
        Route::post('del', 'promotion.coupon/del');
        // 优惠券批量操作
        Route::post('batch', 'promotion.coupon/batch');
    });
    // 积分商品管理
    Route::group('points_exchange', function () {
        // 积分商品列表
        Route::get('list', 'promotion.pointsExchange/list');
        // 积分商品详情
        Route::get('detail', 'promotion.pointsExchange/detail');
        // 积分商品添加
        Route::post('create', 'promotion.pointsExchange/create');
        // 积分商品编辑
        Route::post('update', 'promotion.pointsExchange/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.pointsExchange/updateField');
        // 删除
        Route::post('del', 'promotion.pointsExchange/del');
        // 批量操作
        Route::post('batch', 'promotion.pointsExchange/batch');
    });
    // 优惠活动管理
    Route::group('product_promotion', function () {
        // 优惠活动列表
        Route::get('list', 'promotion.productPromotion/list');
        // 优惠活动配置
        Route::get('config', 'promotion.productPromotion/config');
        // 优惠活动详情
        Route::get('detail', 'promotion.productPromotion/detail');
        // 优惠活动添加
        Route::post('create', 'promotion.productPromotion/create');
        // 优惠活动编辑
        Route::post('update', 'promotion.productPromotion/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.productPromotion/updateField');
        // 优惠活动删除
        Route::post('del', 'promotion.productPromotion/del');
        // 批量操作
        Route::post('batch', 'promotion.productPromotion/batch');
    });
    // 余额充值
    Route::group('recharge_setting', function () {
        // 列表
        Route::get('list', 'promotion.rechargeSetting/list');
        // 详情
        Route::get('detail', 'promotion.rechargeSetting/detail');
        // 添加
        Route::post('create', 'promotion.rechargeSetting/create');
        // 编辑
        Route::post('update', 'promotion.rechargeSetting/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.rechargeSetting/updateField');
        // 删除
        Route::post('del', 'promotion.rechargeSetting/del');
        // 批量操作
        Route::post('batch', 'promotion.rechargeSetting/batch');
    });
    // 秒杀活动
    Route::group('seckill', function () {
        // 列表
        Route::get('list', 'promotion.seckill/list');
        // 装修秒杀列表
        Route::get('list_for_decorate', 'promotion.seckill/listForDecorate');
        // 详情
        Route::get('detail', 'promotion.seckill/detail');
        // 添加
        Route::post('create', 'promotion.seckill/create');
        // 编辑
        Route::post('update', 'promotion.seckill/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.seckill/updateField');
        // 删除
        Route::post('del', 'promotion.seckill/del');
        // 批量操作
        Route::post('batch', 'promotion.seckill/batch');
    });
    // 积分签到
    Route::group('sign_in_setting', function () {
        // 列表
        Route::get('list', 'promotion.signInSetting/list');
        // 详情
        Route::get('detail', 'promotion.signInSetting/detail');
        // 添加
        Route::post('create', 'promotion.signInSetting/create');
        // 编辑
        Route::post('update', 'promotion.signInSetting/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.signInSetting/updateField');
        // 删除
        Route::post('del', 'promotion.signInSetting/del');
        // 批量操作
        Route::post('batch', 'promotion.signInSetting/batch');
    });
});
