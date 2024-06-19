<?php
use think\facade\Route;

// 商品模块
Route::group('product', function () {
    // 品牌
    Route::group('brand', function () {
        // 品牌列表
        Route::get('list', 'product.brand/list');
        // 品牌详情
        Route::get('detail', 'product.brand/detail');
        // 品牌添加
        Route::post('create', 'product.brand/create');
        // 品牌编辑
        Route::post('update', 'product.brand/update');
        // 选择品牌
        Route::get('search', 'product.brand/search');
        // 品牌删除
        Route::post('del', 'product.brand/del');
        // 更新字段
        Route::post('update_field', 'product.brand/updateField');
        // batch批量操作
        Route::post('batch', 'product.brand/batch');
        // 批量更新首字母
        Route::post('update_first_word', 'product.brand/updateFirstWord');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'brandManage'
    ]);
    // 分类
    Route::group('category', function () {
        // 列表
        Route::get('list', 'product.category/list');
        // 商品转移
        Route::post('move_cat', 'product.category/moveCat');
        // 添加
        Route::post('create', 'product.category/create');
        // 编辑
        Route::post('update', 'product.category/update');
        // 选择分类
        Route::get('get_all_category', 'product.category/getAllCategory');
        // 删除
        Route::post('del', 'product.category/del');
        // 更新字段
        Route::post('update_field', 'product.category/updateField');
        // batch批量操作
        Route::post('batch', 'product.category/batch');
        // 详情
        Route::get('detail', 'product.category/detail');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'categoryManage'
    ]);
    // 评论
    Route::group('comment', function () {
        // 列表
        Route::get('list', 'product.comment/list');
        // 编辑
        Route::post('create', 'product.comment/create');
        // 编辑
        Route::post('update', 'product.comment/update');
        // 回复评论
        Route::post('reply_comment', 'product.comment/replyComment');
        // 删除
        Route::post('del', 'product.comment/del');
        // 更新字段
        Route::post('update_field', 'product.comment/updateField');
        // batch批量操作
        Route::post('batch', 'product.comment/batch');
        // 详情
        Route::get('detail', 'product.comment/detail');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'commentManage'
    ]);
    // 商品管理
    Route::group('product', function () {
        // 商品列表
        Route::get('list', 'product.product/list');
        // 商品详情
        Route::get('detail', 'product.product/detail');
        // 商品新增
        Route::post('create', 'product.product/create');
        // 商品复制
        Route::post('copy', 'product.product/copy');
        // 商品配置型词典
        Route::get('config', 'product.product/config');
        // 商品编辑
        Route::post('update', 'product.product/update');
        // 商品删除
        Route::post('del', 'product.product/del');
        // 商品分词
        Route::post('get_participle', 'product.product/getParticiple');
        // 运费模板列表
        Route::get('shipping_tpl_list', 'product.product/shippingTplList');
        // 更新字段
        Route::post('update_field', 'product.product/updateField');
        // 回收站
        Route::post('recycle', 'product.product/recycle');
        // 批量操作
        Route::post('batch', 'product.product/batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productManage'
    ]);
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
        Route::get('list', 'product.productAttributes/list');
        // 详情
        Route::get('detail', 'product.productAttributes/detail');
        // 编辑
        Route::post('create', 'product.productAttributes/create');
        // 编辑
        Route::post('update', 'product.productAttributes/update');
        // 删除
        Route::post('del', 'product.productAttributes/del');
        // 更新字段
        Route::post('update_field', 'product.productAttributes/updateField');
        // batch批量操作
        Route::post('batch', 'product.productAttributes/batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productAttributesTplManage'
    ]);
    // 商品属性模板
    Route::group('product_attributes_tpl', function () {
        // 列表
        Route::get('list', 'product.productAttributesTpl/list');
        // 详情
        Route::get('detail', 'product.productAttributesTpl/detail');
        // 编辑
        Route::post('create', 'product.productAttributesTpl/create');
        // 编辑
        Route::post('update', 'product.productAttributesTpl/update');
        // 删除
        Route::post('del', 'product.productAttributesTpl/del');
        // 更新字段
        Route::post('update_field', 'product.productAttributesTpl/updateField');
        // batch批量操作
        Route::post('batch', 'product.productAttributesTpl/batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productAttributesTplManage'
    ]);
    // 商品库存日志
    Route::group('product_inventory_log', function () {
        // 列表
        Route::get('list', 'product.productInventoryLog/list');
        // 删除
        Route::post('del', 'product.productAttributesTpl/del');
        // batch批量操作
        Route::post('batch', 'product.productAttributesTpl/batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productInventoryLogManage'
    ]);
    // 商品批量处理
    Route::group('product_batch', function () {
        // 图片批量处理
        Route::get('product_batch_deal', 'product.productBatch/productBatchDeal');
        // 商品批量上传 / 修改
        Route::post('product_batch_modify', 'product.productBatch/productBatchModify');
        // 批量修改商品
        Route::post('product_batch_edit', 'product.productBatch/productBatchEdit');
        // 下载模版文件
        Route::post('download_template', 'product.productBatch/downloadTemplate');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productBatch'
    ]);
    // 商品服务
    Route::group('product_services', function () {
        // 列表
        Route::get('list', 'product.productServices/list');
        // 详情
        Route::get('detail', 'product.productServices/detail');
        // 编辑
        Route::post('create', 'product.productServices/create');
        // 编辑
        Route::post('update', 'product.productServices/update');
        // 删除
        Route::post('del', 'product.productServices/del');
        // 更新字段
        Route::post('update_field', 'product.productServices/updateField');
        // batch批量操作
        Route::post('batch', 'product.productServices/batch');
    })->append([
        //用于权限校验的名称
        'authorityCheckAppendGroupName' => 'productServicesManage'
    ]);
});