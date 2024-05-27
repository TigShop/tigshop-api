<?php
use think\facade\Route;

// 店铺
Route::group('store', function () {
    // 日志
    Route::group('store', function () {
        // 列表
        Route::get('list', 'store.store/list');
        // 列表
        Route::get('all', 'store.store/all');
        // 详情
        Route::get('detail', 'store.store/detail');
        // 编辑
        Route::post('create', 'store.store/create');
        // 编辑
        Route::post('update', 'store.store/update');
        // 删除
        Route::post('del', 'store.store/del');
        // 更新字段
        Route::post('update_field', 'store.store/updateField');
        // batch批量操作
        Route::post('batch', 'store.store/batch');
    });
});
