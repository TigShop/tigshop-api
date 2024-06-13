<?php

use think\facade\Route;

// 装修组
Route::group('decorate', function () {
    // 装修管理
    Route::group('decorate', function () {
        // 装修列表
        Route::get('list', 'decorate.decorate/list')->append([
            //此处因为移动端装修权限和pc端装修权限单独设置但都是请求这个接口。所以不能在这拦截权限，需要代码控制器里控制
            // 'authorityCheckAppendName' => 'pcDecorate'
        ]);
        // 装修详情
        Route::get('detail', 'decorate.decorate/detail');
        // 草稿数据
        Route::get('load_draft_data', 'decorate.decorate/loadDraftData');
        // 存入草稿
        Route::post('save_draft', 'decorate.decorate/saveDraft');
        // 发布
        Route::post('publish', 'decorate.decorate/publish');
        // 装修添加
        Route::post('create', 'decorate.decorate/create');
        // 装修编辑
        Route::post('update', 'decorate.decorate/update');
        // 更新字段
        Route::post('update_field', 'decorate.decorate/updateField');
        // 装修删除
        Route::post('del', 'decorate.decorate/del');
        // 批量操作
        Route::post('batch', 'decorate.decorate/batch');
    });
    // 装修模块管理
    Route::group('decorate_discrete', function () {
        // 装修模块详情
        Route::get('detail', 'decorate.decorateDiscrete/detail')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'pcDecorateOtherManage'
        ]);
        // 装修模块编辑
        Route::post('update', 'decorate.decorateDiscrete/update');
    });
    // 装修异步请求
    Route::group('decorate_request', function () {
        // 获取商品列表
        Route::get('product_list', 'decorate.decorateRequest/productList');
    });
    // 首页分类栏
    Route::group('mobile_cat_nav', function () {
        // 首页分类栏列表
        Route::get('list', 'decorate.mobileCatNav/list');
        // 首页分类栏详情
        Route::get('detail', 'decorate.mobileCatNav/detail');
        // 首页分类栏添加
        Route::post('create', 'decorate.mobileCatNav/create');
        // 首页分类栏编辑
        Route::post('update', 'decorate.mobileCatNav/update');
        // 更新字段
        Route::post('update_field', 'decorate.mobileCatNav/updateField');
        // 首页分类栏删除
        Route::post('del', 'decorate.mobileCatNav/del');
        // 批量操作
        Route::post('batch', 'decorate.mobileCatNav/batch');
    });
    // 首页装修模板
    Route::group('mobile_decorate', function () {
        // 首页装修模板列表
        Route::get('list', 'decorate.mobileDecorate/list');
        // 首页装修模板详情
        Route::get('detail', 'decorate.mobileDecorate/detail');
        // 首页装修模板添加
        Route::post('create', 'decorate.mobileDecorate/create');
        // 首页装修模板编辑
        Route::post('update', 'decorate.mobileDecorate/update');
        // 设置为首页
        Route::post('set_home', 'decorate.mobileDecorate/setHome');
        // 复制
        Route::post('copy', 'decorate.mobileDecorate/copy');
        // 更新字段
        Route::post('update_field', 'decorate.mobileDecorate/updateField');
        // 删除
        Route::post('del', 'decorate.mobileDecorate/del');
        // 批量操作
        Route::post('batch', 'decorate.mobileDecorate/batch');
    });
    // PC分类抽屉
    Route::group('pc_cat_floor', function () {
        // PC分类抽屉列表
        Route::get('list', 'decorate.pcCatFloor/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'pcCatFloorManage'
        ]);
        // PC分类抽屉详情
        Route::get('detail', 'decorate.pcCatFloor/detail');
        // PC分类抽屉添加
        Route::post('create', 'decorate.pcCatFloor/create');
        // PC分类抽屉编辑
        Route::post('update', 'decorate.pcCatFloor/update');
        // 更新字段
        Route::post('update_field', 'decorate.pcCatFloor/updateField');
        // PC分类抽屉删除
        Route::post('del', 'decorate.pcCatFloor/del');
        // 批量操作
        Route::post('batch', 'decorate.pcCatFloor/batch');
    });
    // PC导航栏
    Route::group('pc_navigation', function () {
        // PC导航栏列表
        Route::get('list', 'decorate.pcNavigation/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'pcNavigationManage'
        ]);
        // PC导航栏详情
        Route::get('detail', 'decorate.pcNavigation/detail');
        // 获取上级导航
        Route::get('get_parent_nav', 'decorate.pcNavigation/getParentNav');
        // 选择链接地址
        Route::get('select_link', 'decorate.pcNavigation/selectLink');
        // PC导航栏添加
        Route::post('create', 'decorate.pcNavigation/create');
        // PC导航栏编辑
        Route::post('update', 'decorate.pcNavigation/update');
        // 更新字段
        Route::post('update_field', 'decorate.pcNavigation/updateField');
        // PC导航栏删除
        Route::post('del', 'decorate.pcNavigation/del');
        // 批量操作
        Route::post('batch', 'decorate.pcNavigation/batch');
    });
})->middleware([
    \app\adminapi\middleware\CheckAuthor::class
]);
