<?php

use think\facade\Route;

// 后台的路由结构一定是：目录/控制器/动作/  解析：目录.控制器/动作/
// 如 product/brand/list 会解析为如product.brand/list
// 如 product/list 会解析为如product.product/list

// 注：不支持数字和符号

Route::group('example/example', function () {
    Route::get('list', 'example.example/list'); //示例列表
    Route::post('create', 'example.example/create'); //新增示例
    Route::post('update', 'example.example/update'); //编辑示例
    Route::post('del', 'example.example/del'); //删除示例
});

// 公共方法组
Route::group('common', function () {
    Route::group('cache_manage', function () {
        // 清除缓存
        Route::post('cleanup', 'common.cacheManage/cleanup');
    });

    Route::group('verification', function () {
        Route::get('captcha', 'common.verification/captcha');
        // 一次验证
        Route::post('check', 'common.verification/check');
    });
});

// 登录
Route::group('login', function () {
    Route::post('signin', 'login.login/signin'); //登录
    Route::post('send_mobile_code', 'login.login/sendMobileCode'); // 获取验证码
});

// 权限组
Route::group('authority', function () {
    // 管理员日志
    Route::group('admin_log', function () {
        // 列表
        Route::get('list', 'authority.adminLog/list');
    });

    // 角色管理
    Route::group('admin_role', function () {
        // 角色列表
        Route::get('list', 'authority.adminRole/list');
        // 角色详情
        Route::get('detail', 'authority.adminRole/detail');
        // 角色添加
        Route::post('create', 'authority.adminRole/create');
        // 角色编辑
        Route::post('update', 'authority.adminRole/update');
        // 角色删除
        Route::post('del', 'authority.adminRole/del');
        // 更新字段
        Route::post('update_field', 'authority.adminRole/updateField');
        // 批量操作
        Route::post('batch', 'authority.adminRole/batch');
    });

    // 管理员
    Route::group('admin_user', function () {
        // 管理员列表
        Route::get('list', 'authority.adminUser/list');
        // 管理员详情
        Route::get('detail', 'authority.adminUser/detail');
        // 管理员添加
        Route::post('create', 'authority.adminUser/create');
        // 管理员编辑
        Route::post('update', 'authority.adminUser/update');
        // 管理员删除
        Route::post('del', 'authority.adminUser/del');
        // 配置
        Route::get('config', 'authority.adminUser/config');
        // 更新字段
        Route::post('update_field', 'authority.adminUser/updateField');
        // 批量操作
        Route::post('batch', 'authority.adminUser/batch');
        // 账户修改
        Route::post('modify_manage_accounts', 'authority.adminUser/modifyManageAccounts');
        // 获取验证码
        Route::get('get_code', 'authority.adminUser/getCode');
    });

    // 权限管理
    Route::group('authority', function () {
        // 权限列表
        Route::get('list', 'authority.authority/list');
        // 权限详情
        Route::get('detail', 'authority.authority/detail');
        // 权限添加
        Route::post('create', 'authority.authority/create');
        // 权限编辑
        Route::post('update', 'authority.authority/update');
        // 获取所有权限
        Route::get('get_all_authority', 'authority.authority/getAllAuthority');
        // 权限删除
        Route::post('del', 'authority.authority/del');
        // 更新字段
        Route::post('update_field', 'authority.authority/updateField');
        // 批量操作
        Route::post('batch', 'authority.authority/batch');
    });

    // 供应商管理
    Route::group('suppliers', function () {
        // 供应商列表
        Route::get('list', 'authority.suppliers/list');
        // 供应商详情
        Route::get('detail', 'authority.suppliers/detail');
        // 供应商添加
        Route::post('create', 'authority.suppliers/create');
        // 供应商编辑
        Route::post('update', 'authority.suppliers/update');
        // 供应商删除
        Route::post('del', 'authority.suppliers/del');
        // 更新字段
        Route::post('update_field', 'authority.suppliers/updateField');
        // 批量操作
        Route::post('batch', 'authority.suppliers/batch');
    });

});

// 文章组
Route::group('content', function () {
    // 文章管理
    Route::group('article', function () {
        // 文章列表
        Route::get('list', 'content.article/list');
        // 文章详情
        Route::get('detail', 'content.article/detail');
        // 文章添加
        Route::post('create', 'content.article/create');
        // 文章编辑
        Route::post('update', 'content.article/update');
        // 文章删除
        Route::post('del', 'content.article/del');
        // 更新字段
        Route::post('update_field', 'content.article/updateField');
        // 批量操作
        Route::post('batch', 'content.article/batch');
    });

    // 文章分类管理
    Route::group('article_category', function () {
        // 文章分类列表
        Route::get('list', 'content.articleCategory/list');
        // 文章分类详情
        Route::get('detail', 'content.articleCategory/detail');
        // 文章分类添加
        Route::post('create', 'content.articleCategory/create');
        // 文章分类编辑
        Route::post('update', 'content.articleCategory/update');
        // 文章分类删除
        Route::post('del', 'content.articleCategory/del');
        // 更新字段
        Route::post('update_field', 'content.articleCategory/updateField');
        // 获取所有分类
        Route::get('tree', 'content.articleCategory/tree');
        // 批量操作
        Route::post('batch', 'content.articleCategory/batch');
    });
});

// 装修组
Route::group('decorate', function () {
    // 装修管理
    Route::group('decorate', function () {
        // 装修列表
        Route::get('list', 'decorate.decorate/list');
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
        Route::get('detail', 'decorate.decorateDiscrete/detail');
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
        Route::get('list', 'decorate.pcCatFloor/list');
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
        Route::get('list', 'decorate.pcNavigation/list');
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
});

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
        // 发票申请添加
        Route::post('create', 'finance.orderInvoice/create');
        // 发票申请编辑
        Route::post('update', 'finance.orderInvoice/update');
        // 更新字段
        Route::post('update_field', 'finance.orderInvoice/updateField');
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

// 统计面板组
Route::group('panel', function () {
    // 面板管理
    Route::group('panel', function () {
        // 面板数据
        Route::get('list', 'panel.panel/list');
        // 一键直达
        Route::get('search_menu', 'panel.panel/searchMenu');
    });
    // 销售统计
    Route::group('sales_statistics', function () {
        // 销售统计数据
        Route::get('list', 'panel.salesStatistics/list');
        // 销售明细
        Route::get('sales_detail', 'panel.salesStatistics/salesDetail');
        // 销售商品明细
        Route::get('sales_product_detail', 'panel.salesStatistics/salesProductDetail');
        // 销售指标
        Route::get('sales_indicators', 'panel.salesStatistics/salesIndicators');
        // 销售排行
        Route::get('sales_ranking', 'panel.salesStatistics/salesRanking');
    });
    // 访问统计
    Route::group('statistics_access', function () {
        // 访问统计数据
        Route::get('access_statistics', 'panel.statisticsAccess/accessStatistics');
    });
    // 会员统计
    Route::group('statistics_user', function () {
        // 新增会员趋势
        Route::get('add_user_trends', 'panel.statisticsUser/addUserTrends');
        // 会员消费排行
        Route::get('user_consumption_ranking', 'panel.statisticsUser/userConsumptionRanking');
        // 用户统计面板
        Route::get('user_statistics_panel', 'panel.statisticsUser/userStatisticsPanel');
    });
});

// 营销组
Route::group('promotion', function () {
    // 优惠券管理
    Route::group('coupon', function () {
        // 优惠券列表
        Route::get('list', 'promotion.coupon/list');
        // 优惠券配置
        Route::get('config', 'promotion.coupon/config');
        // 优惠券详情
        Route::get('detail', 'promotion.coupon/detail');
        // 优惠券添加
        Route::post('create', 'promotion.coupon/create');
        // 优惠券编辑
        Route::post('update', 'promotion.coupon/update');
        // 更新字段
        Route::post('update_field', 'promotion.coupon/updateField');
        // 优惠券删除
        Route::post('del', 'promotion.coupon/del');
        // 优惠券批量操作
        Route::post('batch', 'promotion.coupon/batch');
    });
    // 积分商品管理
    Route::group('points_exchange', function () {
        // 积分商品列表
        Route::get('list', 'promotion.pointsExchange/list');
        // 积分商品详情
        Route::get('detail', 'promotion.pointsExchange/detail');
        // 积分商品添加
        Route::post('create', 'promotion.pointsExchange/create');
        // 积分商品编辑
        Route::post('update', 'promotion.pointsExchange/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.pointsExchange/updateField');
        // 删除
        Route::post('del', 'promotion.pointsExchange/del');
        // 批量操作
        Route::post('batch', 'promotion.pointsExchange/batch');
    });
    // 优惠活动管理
    Route::group('product_promotion', function () {
        // 优惠活动列表
        Route::get('list', 'promotion.productPromotion/list');
        // 优惠活动配置
        Route::get('config', 'promotion.productPromotion/config');
        // 优惠活动详情
        Route::get('detail', 'promotion.productPromotion/detail');
        // 优惠活动添加
        Route::post('create', 'promotion.productPromotion/create');
        // 优惠活动编辑
        Route::post('update', 'promotion.productPromotion/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.productPromotion/updateField');
        // 优惠活动删除
        Route::post('del', 'promotion.productPromotion/del');
        // 批量操作
        Route::post('batch', 'promotion.productPromotion/batch');
    });
    // 余额充值
    Route::group('recharge_setting', function () {
        // 列表
        Route::get('list', 'promotion.rechargeSetting/list');
        // 详情
        Route::get('detail', 'promotion.rechargeSetting/detail');
        // 添加
        Route::post('create', 'promotion.rechargeSetting/create');
        // 编辑
        Route::post('update', 'promotion.rechargeSetting/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.rechargeSetting/updateField');
        // 删除
        Route::post('del', 'promotion.rechargeSetting/del');
        // 批量操作
        Route::post('batch', 'promotion.rechargeSetting/batch');
    });
    // 秒杀活动
    Route::group('seckill', function () {
        // 列表
        Route::get('list', 'promotion.seckill/list');
        // 详情
        Route::get('detail', 'promotion.seckill/detail');
        // 添加
        Route::post('create', 'promotion.seckill/create');
        // 编辑
        Route::post('update', 'promotion.seckill/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.seckill/updateField');
        // 删除
        Route::post('del', 'promotion.seckill/del');
        // 批量操作
        Route::post('batch', 'promotion.seckill/batch');
    });
    // 积分签到
    Route::group('sign_in_setting', function () {
        // 列表
        Route::get('list', 'promotion.signInSetting/list');
        // 详情
        Route::get('detail', 'promotion.signInSetting/detail');
        // 添加
        Route::post('create', 'promotion.signInSetting/create');
        // 编辑
        Route::post('update', 'promotion.signInSetting/update');
        // 更新单个字段
        Route::post('update_field', 'promotion.signInSetting/updateField');
        // 删除
        Route::post('del', 'promotion.signInSetting/del');
        // 批量操作
        Route::post('batch', 'promotion.signInSetting/batch');
    });
});

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
        Route::get('list', 'setting.config/list');
        // 基础设置更新
        Route::post('save', 'setting.config/save');
        // 添加
        Route::post('create', 'setting.config/create');
        // 编辑
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

//订单模块
Route::group('order', function () {
    //售后管理
    Route::group('aftersales', function () {
        // 列表
        Route::get('list', 'order.aftersales/list');
        // 详情接口
        Route::get('detail', 'order.aftersales/detail');
        // 同意或拒接售后接口
        Route::post('update', 'order.aftersales/update');
        // 售后确认收货接口
        Route::post('receive', 'order.aftersales/receive');
        // 更新单个字段
        Route::post('update_field', 'order.aftersales/updateField');
        // 售后确认收货接口
        Route::post('receive', 'order.aftersales/receive');
        // 提交售后反馈记录
        Route::post('record', 'order.aftersales/record');
    });
    //订单管理
    Route::group('order', function () {
        //订单列表
        Route::get('list', 'order.order/list');
        //订单详情
        Route::get('detail', 'order.order/detail');
        //订单发货
        Route::post('deliver', 'order.order/deliver');
        //订单收货
        Route::post('confirm_receipt', 'order.order/confirmReceipt');
        //订单更新
        Route::post('update', 'order.order/update');
        //订单修改收货人信息
        Route::post('modify_consignee', 'order.order/modifyConsignee');
        //修改配送信息
        Route::post('modify_shipping', 'order.order/modifyShipping');
        //修改订单金额
        Route::post('modify_money', 'order.order/modifyMoney');
        //修改订单金额
        Route::post('modify_money', 'order.order/modifyMoney');
        //取消订单
        Route::post('cancel_order', 'order.order/cancelOrder');
        //订单设置为已确认
        Route::post('set_confirm', 'order.order/setConfirm');
        //订单软删除
        Route::post('del_order', 'order.order/delOrder');
        //订单拆分
        Route::post('split_store_order', 'order.order/splitStoreOrder');
        //订单拆分
        Route::post('set_paid', 'order.order/setPaid');
        //修改商品信息
        Route::post('modify_product', 'order.order/modifyProduct');
        //添加商品时获取商品信息
        Route::get('get_add_product_info', 'order.order/getAddProductInfo');
        //添加商品时获取商品信息
        Route::post('set_admin_note', 'order.order/setAdminNote');
        //打印订单
        Route::get('order_print', 'order.order/orderPrint');
        //订单导出标签列表
        Route::get('get_export_item_list', 'order.order/getExportItemList');
        //订单导出存的标签
        Route::post('save_export_item', 'order.order/saveExportItem');
        //标签详情
        Route::get('export_item_info', 'order.order/exportItemInfo');
        //订单导出
        Route::get('order_export', 'order.order/orderExport');
    });
    //日志管理
    Route::group('order_log', function () {
        // 列表
        Route::get('list', 'order.orderLog/list');
        // 详情接口
        Route::get('detail', 'order.orderLog/detail');
        // 同意或拒接售后接口
        Route::post('update', 'order.orderLog/update');
        // 更新单个字段
        Route::post('update_field', 'order.orderLog/updateField');
    });
});
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
    });
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
    });
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
    });
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
    });
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
    });
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
    });
    // 商品库存日志
    Route::group('product_inventory_log', function () {
        // 列表
        Route::get('list', 'product.productInventoryLog/list');
        // 删除
        Route::post('del', 'product.productAttributesTpl/del');
        // batch批量操作
        Route::post('batch', 'product.productAttributesTpl/batch');
    });
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
    });
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
    });
});

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

// 会员管理模块
Route::group('user', function () {

    // 会员留言
    Route::group('feedback', function () {
        // 列表
        Route::get('list', 'user.feedback/list');
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
        Route::get('list', 'user.user/list');
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
        Route::get('list', 'user.userMessageLog/list');
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
        Route::get('list', 'user.userPointsLog/list');
        // 删除
        Route::post('del', 'user.userPointsLog/del');
        // batch批量操作
        Route::post('batch', 'user.userPointsLog/batch');
    });
    // 会员等级
    Route::group('user_rank', function () {
        // 列表
        Route::get('list', 'user.userRank/list');
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

});
