<?php

use think\facade\Route;

//订单模块
Route::group('order', function () {
    //售后管理
    Route::group('aftersales', function () {
        // 列表
        Route::get('list', 'order.aftersales/list');
        // 详情接口
        Route::get('detail', 'order.aftersales/detail');
        // 同意或拒接售后接口
        Route::post('update', 'order.aftersales/update');
        // 售后确认收货接口
        Route::post('receive', 'order.aftersales/receive');
        // 提交售后反馈记录
        Route::post('record', 'order.aftersales/record');
    });
    //订单管理
    Route::group('order', function () {
        //订单列表
        Route::get('list', 'order.order/list');
        //订单详情
        Route::get('detail', 'order.order/detail');
        //订单发货
        Route::post('deliver', 'order.order/deliver');
        //订单收货
        Route::post('confirm_receipt', 'order.order/confirmReceipt');
        //订单修改收货人信息
        Route::post('modify_consignee', 'order.order/modifyConsignee');
        //修改配送信息
        Route::post('modify_shipping', 'order.order/modifyShipping');
        //修改订单金额
        Route::post('modify_money', 'order.order/modifyMoney');
        //取消订单
        Route::post('cancel_order', 'order.order/cancelOrder');
        //订单设置为已确认
        Route::post('set_confirm', 'order.order/setConfirm');
        //订单软删除
        Route::post('del_order', 'order.order/delOrder');
        //订单拆分
        Route::post('split_store_order', 'order.order/splitStoreOrder');
        //订单设置为已支付
        Route::post('set_paid', 'order.order/setPaid');
        //修改商品信息
        Route::post('modify_product', 'order.order/modifyProduct');
        //添加商品时获取商品信息
        Route::post('get_add_product_info', 'order.order/getAddProductInfo');
        //设置商家备注
        Route::post('set_admin_note', 'order.order/setAdminNote');
        //打印订单
        Route::get('order_print', 'order.order/orderPrint');
        //订单导出标签列表
        Route::get('get_export_item_list', 'order.order/getExportItemList');
        //订单导出存的标签
        Route::post('save_export_item', 'order.order/saveExportItem');
        //标签详情
        Route::get('export_item_info', 'order.order/exportItemInfo');
        //订单导出
        Route::get('order_export', 'order.order/orderExport');
    });
    //日志管理
    Route::group('order_log', function () {
        // 列表
        Route::get('list', 'order.orderLog/list');
        // 添加日志
        Route::post('create', 'order.orderLog/create');
    });
});