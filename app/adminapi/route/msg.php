<?php
use think\facade\Route;

// 消息管理组
Route::group('msg', function () {
    // 管理员消息
    Route::group('admin_msg', function () {
        // 列表
        Route::get('list', 'msg.adminMsg/list');
        // 设置单个已读
        Route::post('set_readed', 'msg.adminMsg/setReaded');
        // 设置全部已读
        Route::post('set_all_readed', 'msg.adminMsg/setAllReaded');
    });
});
