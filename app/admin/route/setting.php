<?php

use think\facade\Route;

// 配置组
Route::group('setting', function () {
    // APP版本管理
    Route::group('app_version', function () {
        // 列表
        Route::get('list', 'setting.appVersion/list');
        // 详情
        Route::get('detail', 'setting.appVersion/detail');
        // 添加
        Route::post('create', 'setting.appVersion/create');
        // 编辑
        Route::post('update', 'setting.appVersion/update');
    });
    // 设置项管理
    Route::group('config', function () {
        // 基础设置
        Route::get('get_base', 'setting.config/get_base');
        // 前端后台设置项
        Route::get('get_admin', 'setting.config/getAdmin');
        // todo 基础设置更新
        Route::post('save', 'setting.config/save');
        // todo 编辑
        Route::post('update', 'setting.config/update');
        // 邮箱服务器设置
        Route::post('save_mail', 'setting.config/saveMail');
        // 获取图标icon
        Route::get('get_icon', 'setting.config/getIcon');
        // 发送测试邮件
        Route::post('send_test_email', 'setting.config/sendTestEmail');
        // 上传API文件
        Route::post('upload_file', 'setting.config/uploadFile');
        // 生成平台证书
        Route::post('create_platform_certificate', 'setting.config/createPlatformCertificate');
    });
    // 计划任务
    Route::group('crons', function () {
        // 列表
        Route::get('list', 'setting.crons/list');
        // 详情
        Route::get('detail', 'setting.crons/detail');
        // 添加
        Route::post('create', 'setting.crons/create');
        // 编辑
        Route::post('update', 'setting.crons/update');
        // 更新单个字段
        Route::post('update_field', 'setting.crons/updateField');
        // 删除
        Route::post('del', 'setting.crons/del');
        // 批量操作
        Route::post('batch', 'setting.crons/batch');
    });
    // 友情链接
    Route::group('friend_links', function () {
        // 列表
        Route::get('list', 'setting.friendLinks/list');
        // 详情
        Route::get('detail', 'setting.friendLinks/detail');
        // 添加
        Route::post('create', 'setting.friendLinks/create');
        // 编辑
        Route::post('update', 'setting.friendLinks/update');
        // 更新单个字段
        Route::post('update_field', 'setting.friendLinks/updateField');
        // 删除
        Route::post('del', 'setting.friendLinks/del');
        // 批量操作
        Route::post('batch', 'setting.friendLinks/batch');
    });
    // 相册
    Route::group('gallery', function () {
        // 列表
        Route::get('list', 'setting.gallery/list');
        // 详情
        Route::get('detail', 'setting.gallery/detail');
        // 添加
        Route::post('create', 'setting.gallery/create');
        // 编辑
        Route::post('update', 'setting.gallery/update');
        // 更新单个字段
        Route::post('update_field', 'setting.gallery/updateField');
        // 删除
        Route::post('del', 'setting.gallery/del');
        // 批量操作
        Route::post('batch', 'setting.gallery/batch');
    });
    // 相册图片
    Route::group('gallery_pic', function () {
        // 列表
        Route::get('list', 'setting.galleryPic/list');
        // 详情
        Route::get('detail', 'setting.galleryPic/detail');
        // 添加
        Route::post('create', 'setting.galleryPic/create');
        // 编辑
        Route::post('update', 'setting.galleryPic/update');
        // 更新单个字段
        Route::post('update_field', 'setting.galleryPic/updateField');
        // 图片上传
        Route::post('upload_img', 'setting.galleryPic/uploadImg');
        // 删除
        Route::post('del', 'setting.galleryPic/del');
        // 批量操作
        Route::post('batch', 'setting.galleryPic/batch');
    });
    // 物流公司
    Route::group('logistics_company', function () {
        // 分页列表
        Route::get('list', 'setting.logisticsCompany/list');
        // 全部列表
        Route::get('get_all', 'setting.logisticsCompany/getAll');
        // 详情
        Route::get('detail', 'setting.logisticsCompany/detail');
        // 添加
        Route::post('create', 'setting.logisticsCompany/create');
        // 编辑
        Route::post('update', 'setting.logisticsCompany/update');
        // 更新单个字段
        Route::post('update_field', 'setting.logisticsCompany/updateField');
        // 删除
        Route::post('del', 'setting.logisticsCompany/del');
        // 批量操作
        Route::post('batch', 'setting.logisticsCompany/batch');
    });
    // 邮件模板设置
    Route::group('mail_templates', function () {
        // 列表
        Route::get('list', 'setting.mailTemplates/list');
        // 详情
        Route::get('detail', 'setting.mailTemplates/detail');
        // 添加
        Route::post('create', 'setting.mailTemplates/create');
        // 编辑
        Route::post('update', 'setting.mailTemplates/update');
        // 删除
        Route::post('del', 'setting.mailTemplates/del');
        // 批量操作
        Route::post('batch', 'setting.mailTemplates/batch');
        // 获取所有的邮件模板
        Route::get('get_all_mail_templates', 'setting.mailTemplates/getAllMailTemplates');
    });
    // 消息设置
    Route::group('message_type', function () {
        // 列表
        Route::get('list', 'setting.messageType/list');
        // 详情
        Route::get('detail', 'setting.messageType/detail');
        // 添加
        Route::post('create', 'setting.messageType/create');
        // 编辑
        Route::post('update', 'setting.messageType/update');
        // 更新单个字段
        Route::post('update_field', 'setting.messageType/updateField');
        // 删除
        Route::post('del', 'setting.messageType/del');
        // 批量操作
        Route::post('batch', 'setting.messageType/batch');
        // 生成小程序消息模板
        Route::post('mini_program_message_template', 'setting.messageType/miniProgramMessageTemplate');
        // 同步小程序消息模板
        Route::post('mini_program_message_template_sync', 'setting.messageType/miniProgramMessageTemplateSync');
    });
    // 地区管理
    Route::group('region', function () {
        // 列表
        Route::get('list', 'setting.region/list');
        // 详情
        Route::get('detail', 'setting.region/detail');
        // 获取地区树
        Route::get('get_region_tree', 'setting.region/getRegionTree');
        // 获取所有地区树
        Route::get('get_all_region_tree', 'setting.region/getAllRegionTree');
        // 获取子地区
        Route::get('get_child_region', 'setting.region/getChildRegion');
        // 添加
        Route::post('create', 'setting.region/create');
        // 编辑
        Route::post('update', 'setting.region/update');
        // 更新单个字段
        Route::post('update_field', 'setting.region/updateField');
        // 删除
        Route::post('del', 'setting.region/del');
        // 批量操作
        Route::post('batch', 'setting.region/batch');
    });
    // 运费模板管理
    Route::group('shipping_tpl', function () {
        // 列表
        Route::get('list', 'setting.shippingTpl/list');
        // 配置型
        Route::get('config', 'setting.shippingTpl/config');
        // 详情
        Route::get('detail', 'setting.shippingTpl/detail');
        // 添加
        Route::post('create', 'setting.shippingTpl/create');
        // 编辑
        Route::post('update', 'setting.shippingTpl/update');
        // 更新单个字段
        Route::post('update_field', 'setting.shippingTpl/updateField');
        // 删除
        Route::post('del', 'setting.shippingTpl/del');
        // 批量操作
        Route::post('batch', 'setting.shippingTpl/batch');
    });
    // 配送类型
    Route::group('shipping_type', function () {
        // 列表
        Route::get('list', 'setting.shippingType/list');
        // 详情
        Route::get('detail', 'setting.shippingType/detail');
        // 添加
        Route::post('create', 'setting.shippingType/create');
        // 编辑
        Route::post('update', 'setting.shippingType/update');
        // 更新单个字段
        Route::post('update_field', 'setting.shippingType/updateField');
        // 删除
        Route::post('del', 'setting.shippingType/del');
        // 批量操作
        Route::post('batch', 'setting.shippingType/batch');
    });
});
