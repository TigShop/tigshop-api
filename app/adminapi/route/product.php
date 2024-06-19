<?php
use think\facade\Route;

// 商品模块
Route::group('product', function () {
    // 品牌
    Route::group('brand', function () {
        // 品牌列表
        Route::get('list', 'list');
        // 品牌详情
        Route::get('detail', 'detail');
        // 品牌添加
        Route::post('create', 'create');
        // 品牌编辑
        Route::post('update', 'update');
        // 选择品牌
        Route::get('search', 'search');
        // 品牌删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
        // 批量更新首字母
        Route::post('update_first_word', 'updateFirstWord');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'brandManage'
    ])->prefix('product.brand/');
    // 分类
    Route::group('category', function () {
        // 列表
        Route::get('list', 'list');
        // 商品转移
        Route::post('move_cat', 'moveCat');
        // 添加
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 选择分类
        Route::get('get_all_category', 'getAllCategory');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
        // 详情
        Route::get('detail', 'detail');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'categoryManage'
    ])->prefix('product.category/');
    // 评论
    Route::group('comment', function () {
        // 列表
        Route::get('list', 'list');
        // 添加
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 回复评论
        Route::post('reply_comment', 'replyComment');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
        // 详情
        Route::get('detail', 'detail');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'commentManage'
    ])->prefix('product.comment/');
    // 商品管理
    Route::group('product', function () {
        // 商品列表
        Route::get('list', 'list');
        // 商品详情
        Route::get('detail', 'detail');
        // 商品新增
        Route::post('create', 'create');
        // 商品复制
        Route::post('copy', 'copy');
        // 商品配置型词典
        Route::get('config', 'config');
        // 商品编辑
        Route::post('update', 'update');
        // 商品删除
        Route::post('del', 'del');
        // 商品分词
        Route::post('get_participle', 'getParticiple');
        // 运费模板列表
        Route::get('shipping_tpl_list', 'shippingTplList');
        // 更新字段
        Route::post('update_field', 'updateField');
        // 回收站
        Route::post('recycle', 'recycle');
        // 批量操作
        Route::post('batch', 'batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productManage'
    ])->prefix('product.product/');
    // 商品分组
    Route::group('product_group', function () {
        // 列表
        Route::get('list', 'list');
        // 编辑
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // batch批量操作
        Route::post('batch', 'batch');
        // 详情
        Route::get('detail', 'detail');
    })->prefix('product.productGroup/')->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productGroupManage'
    ]);
    // 商品属性
    Route::group('product_attributes', function () {
        // 列表
        Route::get('list', 'list');
        // 详情
        Route::get('detail', 'detail');
        // 编辑
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productAttributesTplManage'
    ])->prefix('product.productAttributes/');
    // 商品属性模板
    Route::group('product_attributes_tpl', function () {
        // 列表
        Route::get('list', 'list');
        // 详情
        Route::get('detail', 'detail');
        // 编辑
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productAttributesTplManage'
    ])->prefix('product.productAttributesTpl/');

    // 商品库存日志
    Route::group('product_inventory_log', function () {
        // 列表
        Route::get('list', 'list');
        // 删除
        Route::post('del', 'del');
        // batch批量操作
        Route::post('batch', 'batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productInventoryLogManage'
    ])->prefix('product.productInventoryLog/');

    // 商品批量处理
    Route::group('product_batch', function () {
        // 图片批量处理
        Route::get('product_batch_deal', 'productBatchDeal');
        // 商品批量上传 / 修改
        Route::post('product_batch_modify', 'productBatchModify');
        // 批量修改商品
        Route::post('product_batch_edit', 'productBatchEdit');
        // 下载模版文件
        Route::post('download_template', 'downloadTemplate');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productBatch'
    ])->prefix('product.productBatch/');
    // 商品服务
    Route::group('product_services', function () {
        // 列表
        Route::get('list', 'list');
        // 详情
        Route::get('detail', 'detail');
        // 编辑
        Route::post('create', 'create');
        // 编辑
        Route::post('update', 'update');
        // 删除
        Route::post('del', 'del');
        // 更新字段
        Route::post('update_field', 'updateField');
        // batch批量操作
        Route::post('batch', 'batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productServicesManage'
    ])->prefix('product.productServices/');
});