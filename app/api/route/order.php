<?php
use think\facade\Route;

// 订单
Route::group('order', function () {

    // 订单结算
    Route::group('check', function () {
        // 结算
        Route::post('index', 'order.check/index');
        // 订单
        Route::post('update', 'order.check/update');
        // 订单
        Route::post('update_coupon', 'order.check/updateCoupon');
        // 订单提交
        Route::post('submit', 'order.check/submit');
        // 获得上次订单发票信息
        Route::get('get_invoice', 'order.check/getInvoice');

    })->middleware([
        \app\api\middleware\CheckLogin::class,
    ]);

    // 订单支付
    Route::group('pay', function () {
        // 支付页信息
        Route::get('index', 'order.pay/index');
        // 订单状态
        Route::get('check_status', 'order.pay/checkStatus');
        // 支付
        Route::post('create', 'order.pay/create');
        // 支付回调
        Route::post('notify', 'order.pay/notify');
        // 退款回调
        Route::post('refund_notify', 'order.pay/refundNotify');
    });
});
