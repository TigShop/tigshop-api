<?php

use think\facade\Route;

// 财务组
Route::group('finance', function () {
    // 账户资金面板
    Route::group('account_panel', function () {
        // 面板
        Route::get('list', 'finance.accountPanel/list');
    });
    // 发票申请
    Route::group('order_invoice', function () {
        // 发票申请列表
        Route::get('list', 'finance.orderInvoice/list');
        // 发票申请详情
        Route::get('detail', 'finance.orderInvoice/detail');
        // 发票申请编辑
        Route::post('update', 'finance.orderInvoice/update');
        // 发票申请删除
        Route::post('del', 'finance.orderInvoice/del');
        // 批量操作
        Route::post('batch', 'finance.orderInvoice/batch');
    });
    // 交易日志
    Route::group('pay_log', function () {
        // 交易日志列表
        Route::get('list', 'finance.payLog/list');
        // 交易日志删除
        Route::post('del', 'finance.payLog/del');
        // 批量操作
        Route::post('batch', 'finance.payLog/batch');
    });
    // 退款申请
    Route::group('refund_apply', function () {
        // 退款申请列表
        Route::get('list', 'finance.refundApply/list');
        // 退款申请详情
        Route::get('detail', 'finance.refundApply/detail');
        // 配置型
        Route::get('config', 'finance.refundApply/config');
        // 退款申请编辑
        Route::post('audit', 'finance.refundApply/audit');
        // 确认线下转账
        Route::post('offline_audit', 'finance.refundApply/offlineAudit');
    });
    // 退款记录
    Route::group('refund_log', function () {
        // 退款记录
        Route::get('list', 'finance.refundLog/list');
    });
    // 余额日志
    Route::group('user_balance_log', function () {
        // 余额日志列表
        Route::get('list', 'finance.userBalanceLog/list');
        // 删除
        Route::post('del', 'finance.userBalanceLog/del');
        // 批量操作
        Route::post('batch', 'finance.userBalanceLog/batch');
    });
    // 增票资质申请
    Route::group('user_invoice', function () {
        // 增票资质申请列表
        Route::get('list', 'finance.userInvoice/list');
        // 配置型
        Route::get('config', 'finance.userInvoice/config');
        // 增票资质申请详情
        Route::get('detail', 'finance.userInvoice/detail');
        // 增票资质申请编辑
        Route::post('update', 'finance.userInvoice/update');
        // 删除
        Route::post('del', 'finance.userInvoice/del');
        // 批量操作
        Route::post('batch', 'finance.userInvoice/batch');
    });
    // 充值申请管理
    Route::group('user_recharge_order', function () {
        // 充值申请列表
        Route::get('list', 'finance.userRechargeOrder/list');
        // 充值申请详情
        Route::get('detail', 'finance.userRechargeOrder/detail');
        // 充值申请添加
        Route::post('create', 'finance.userRechargeOrder/create');
        // 充值申请编辑
        Route::post('update', 'finance.userRechargeOrder/update');
        // 删除
        Route::post('del', 'finance.userRechargeOrder/del');
        // 批量操作
        Route::post('batch', 'finance.userRechargeOrder/batch');
    });
    // 提现申请
    Route::group('user_withdraw_apply', function () {
        // 提现申请列表
        Route::get('list', 'finance.userWithdrawApply/list');
        // 提现申请详情
        Route::get('detail', 'finance.userWithdrawApply/detail');
        // 提现申请添加
        Route::post('create', 'finance.userWithdrawApply/create');
        // 提现申请编辑
        Route::post('update', 'finance.userWithdrawApply/update');
        // 删除
        Route::post('del', 'finance.userWithdrawApply/del');
        // 批量操作
        Route::post('batch', 'finance.userWithdrawApply/batch');
    });
});