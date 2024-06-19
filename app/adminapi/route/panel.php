<?php

use think\facade\Route;

// 统计面板组
Route::group('panel', function () {
    // 面板管理
    Route::group('panel', function () {
        // 面板数据
        Route::get('index', 'panel.panel/index');
        // 一键直达
        Route::get('search_menu', 'panel.panel/searchMenu');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'consoleManage'
    ]);
    // 销售统计
    Route::group('sales_statistics', function () {
        // 销售统计数据
        Route::get('list', 'panel.salesStatistics/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendGroupName' => 'statisticsOrder'
        ]);
        // 销售明细
        Route::get('sales_detail', 'panel.salesStatistics/salesDetail')->append([
            //用于权限校验的名称
            'authorityCheckAppendGroupName' => 'statisticsSale'
        ]);
        // 销售商品明细
        Route::get('sales_product_detail', 'panel.salesStatistics/salesProductDetail')->append([
            //用于权限校验的名称
            'authorityCheckAppendGroupName' => 'statisticsSale'
        ]);
        // 销售指标
        Route::get('sales_indicators', 'panel.salesStatistics/salesIndicators')->append([
            //用于权限校验的名称
            'authorityCheckAppendGroupName' => 'saleTargets'
        ]);
        // 销售排行
        Route::get('sales_ranking', 'panel.salesStatistics/salesRanking')->append([
            //用于权限校验的名称
            'authorityCheckAppendGroupName' => 'consumerRanking'
        ]);
    });
    // 访问统计
    Route::group('statistics_access', function () {
        // 访问统计数据
        Route::get('access_statistics', 'panel.statisticsAccess/accessStatistics');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'statisticsAccess'
    ]);
    // 会员统计
    Route::group('statistics_user', function () {
        // 新增会员趋势
        Route::get('add_user_trends', 'panel.statisticsUser/addUserTrends');
        // 会员消费排行
        Route::get('user_consumption_ranking', 'panel.statisticsUser/userConsumptionRanking');
        // 用户统计面板
        Route::get('user_statistics_panel', 'panel.statisticsUser/userStatisticsPanel');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'newMembers'
    ]);
});
