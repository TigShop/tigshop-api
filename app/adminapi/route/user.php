<?php

use think\facade\Route;

// 会员管理模块
Route::group('user', function () {
    // 会员留言
    Route::group('feedback', function () {
        // 列表
        Route::get('list', 'user.feedback/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'feedbackManage'
        ]);
        // 详情
        Route::get('detail', 'user.feedback/detail');
        // 编辑
        Route::post('create', 'user.feedback/create');
        // 编辑
        Route::post('update', 'user.feedback/update');
        // 删除
        Route::post('del', 'user.feedback/del');
        // 更新字段
        Route::post('update_field', 'user.feedback/updateField');
        // batch批量操作
        Route::post('batch', 'user.feedback/batch');
    });
    // 会员
    Route::group('user', function () {
        // 列表
        Route::get('list', 'user.user/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'userManage'
        ]);
        // 详情
        Route::get('detail', 'user.user/detail');
        // 编辑
        Route::post('create', 'user.user/create');
        // 编辑
        Route::post('update', 'user.user/update');
        // 删除
        Route::post('del', 'user.user/del');
        // 更新字段
        Route::post('update_field', 'user.user/updateField');
        // batch批量操作
        Route::post('batch', 'user.user/batch');
        // 资金明细
        Route::get('user_fund_detail', 'user.user/userFundDetail');
        // 资金管理
        Route::post('fund_management', 'user.user/fundManagement');
    });
    // 会员日志
    Route::group('user_message_log', function () {
        // 列表
        Route::get('list', 'user.userMessageLog/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'messageLogManage'
        ]);
        // 列表
        Route::get('detail', 'user.userMessageLog/detail');
        // 新增
        Route::post('create', 'user.userMessageLog/create');
        // 编辑
        Route::post('update', 'user.userMessageLog/update');
        // 删除
        Route::post('del', 'user.userMessageLog/del');
        // 撤回
        Route::post('recall', 'user.userMessageLog/recall');
    });
    // 会员积分日志
    Route::group('user_points_log', function () {
        // 列表
        Route::get('list', 'user.userPointsLog/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'integralLogManage'
        ]);
        // 删除
        Route::post('del', 'user.userPointsLog/del');
        // batch批量操作
        Route::post('batch', 'user.userPointsLog/batch');
    });
    // 会员等级
    Route::group('user_rank', function () {
        // 列表
        Route::get('list', 'user.userRank/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'userRankManage'
        ]);
        // 详情
        Route::get('detail', 'user.userRank/detail');
        // 编辑
        Route::post('create', 'user.userRank/create');
        // 编辑
        Route::post('update', 'user.userRank/update');
        // 删除
        Route::post('del', 'user.userRank/del');
        // 更新字段
        Route::post('update_field', 'user.userRank/updateField');
        // batch批量操作
        Route::post('batch', 'user.userRank/batch');
    });
})->middleware([
    \app\adminapi\middleware\CheckAuthor::class
]);
