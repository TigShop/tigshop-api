<?php

use think\facade\Route;

// 访问日志控制器
Route::group('sys', function () {
    // 日志
    Route::group('access_log', function () {
        // 列表
        Route::get('list', 'sys.accessLog/list');
        // 详情
        Route::get('detail', 'sys.accessLog/detail');
        // 编辑
        Route::post('create', 'sys.accessLog/create');
        // 编辑
        Route::post('update', 'sys.accessLog/update');
        // 删除
        Route::post('del', 'sys.accessLog/del');
        // 更新字段
        Route::post('update_field', 'sys.accessLog/updateField');
        // batch批量操作
        Route::post('batch', 'sys.accessLog/batch');
    });
});
