<?php


use think\facade\Route;

// 后台的路由结构一定是：目录/控制器/动作/ 或 目录/控制器/动作.html
// 如 product/brand/list.html 会解析为如product.brand/list.html

// 注：不支持数字和符号
// 订单
Route::group('order', function () {

    // 订单结算
    Route::group('check', function () {
        // 结算
        Route::post('index', 'order.check/index');
        // 订单
        Route::post('update', 'order.check/update');
        // 订单
        Route::post('update_coupon', 'order.check/updateCoupon');
        // 订单提交
        Route::post('submit', 'order.check/submit');
        // 获得上次订单发票信息
        Route::get('get_invoice', 'order.check/getInvoice');

    });

    // 订单支付
    Route::group('pay', function () {
        // 支付页信息
        Route::get('index', 'order.pay/index');
        // 订单状态
        Route::get('check_status', 'order.pay/checkStatus');
        // 支付
        Route::post('create', 'order.pay/create');
        // 回调
        Route::post('notify', 'order.pay/notify');
        // 回调
        Route::post('notify', 'order.pay/notify');
        // 回调
        Route::post('refund_notify', 'order.pay/refundNotify');
    });
});


Route::group('product', function () {

    //兑换
    Route::group('exchange', function () {
        // 列表
        Route::get('list', 'product.exchange/list');
        // 详情
        Route::get('detail', 'product.exchange/detail');

    });

    // 商品
    Route::group('product', function () {
        // 详情
        Route::get('detail', 'product.product/detail');
        // 评论
        Route::get('get_comment', 'product.product/getComment');
        // 评论列表
        Route::get('get_comment_list', 'product.product/getCommentList');
        // 咨询列表
        Route::get('get_feedback_list', 'product.product/getFeedbackList');
        // 可用信息sku和活动等
        Route::get('get_product_availability', 'product.product/getProductAvailability');
        // 列表
        Route::get('list', 'product.product/list');
        // 优惠卷
        Route::get('get_coupon', 'product.product/getCouponList');
        // 是否收藏
        Route::get('is_collect', 'product.product/isCollect');
        //加入购物车
        Route::post('add_to_cart', 'product.product/addToCart');

    });
});

// 公共方法
Route::group('common', function () {
    // 配置
    Route::group('config', function () {
        // 基本配置
        Route::get('base', 'common.config/base');
        // 售后服务配置
        Route::get('after_sales_service', 'common.config/afterSalesService');
    });

    // PC
    Route::group('pc', function () {
        // 获取头部导航
        Route::get('get_header', 'common.pc/getHeader');
        // 获取PC导航栏
        Route::get('get_nav', 'common.pc/getNav');
        // 获取PC分类抽屉
        Route::get('get_cat_floor', 'common.pc/getCatFloor');
    });
    // PC
    Route::group('util', function () {
        // 获取头部导航
        Route::get('qr_code', 'common.util/qrCode');
    });
    // 推荐位
    Route::group('recommend', function () {
        // 猜你喜欢
        Route::get('guess_like', 'common.recommend/guessLike');
    });
    // 验证
    Route::group('verification', function () {
        // 获取验证码
        Route::post('captcha', 'common.verification/captcha');
        // 一次验证
        Route::post('check', 'common.verification/check');
        // 二次验证
        Route::post('verification', 'common.verification/verification');
    });
});

// 文章
Route::group('article', function () {
    // 文章
    Route::group('article', function () {
        // 文章列表
        Route::get('list', 'article.article/list');
        // 资讯类文章详情
        Route::get('news_info', 'article.article/newsInfo');
        // 帮助类文章详情
        Route::get('issue_info', 'article.article/issueInfo');
    });
    // 文章分类
    Route::group('category', function () {
        // 文章分类
        Route::get('list', 'article.category/list');
        // 首页帮助分类与文章
        Route::get('index_bzzx_list', 'article.category/indexBzzxList');
    });
});

// 购物车
Route::group('cart', function () {
    // 购物车
    Route::group('cart', function () {
        // 购物车列表
        Route::get('list', 'cart.cart/list');
        // 获取购物车商品数量
        Route::get('get_count', 'cart.cart/getCount');
        // 更新购物车商品选择状态
        Route::post('update_check', 'cart.cart/updateCheck');
        // 更新购物车商品数量
        Route::post('update_item', 'cart.cart/updateItem');
        // 删除购物车商品
        Route::post('remove_item', 'cart.cart/removeItem');
        // 清空购物车
        Route::post('clear', 'cart.cart/clear');
    });
});

// 商品分类
Route::group('category', function () {
    // 商品分类
    Route::group('category', function () {
        // 获取当前分类的父级分类
        Route::get('parent_tree', 'category.category/parentTree');
        // 获取所有分类
        Route::get('all', 'category.category/all');
        // 商品相关分类
        Route::get('relate_info', 'category.category/relateInfo');
        // 商品相关分类
        Route::get('relate_category', 'category.category/getRelateCategory');
        // 商品相关品牌
        Route::get('relate_brand', 'category.category/getRelateBrand');
        // 商品相关排行
        Route::get('relate_rank', 'category.category/getRelateRank');
        // 商品相关文章
        Route::get('relate_article', 'category.category/getRelateArticle');
        // 商品相关排行
        Route::get('relate_look_also', 'category.category/getRelateLookAlso');
        // 热门分类
        Route::get('hot', 'category.category/hot');
    });
});

// 首页
Route::group('home', function () {
    // 首页
    Route::group('home', function () {
        // 首页
        Route::get('index', 'home.home/index');
        // PC首页
        Route::get('pc_index', 'home.home/pcIndex');
        // 首页今日推荐
        Route::get('get_recommend', 'home.home/getRecommend');
        // 首页秒杀
        Route::get('get_seckill', 'home.home/getSeckill');
        // 首页优惠券
        Route::get('get_coupon', 'home.home/getCoupon');
        // 首页分类栏
        Route::get('mobile_cat_nav', 'home.home/mobileCatNav');
        // 移动端导航栏
        Route::get('mobile_nav', 'home.home/mobileNav');
        // 客服
        Route::get('kefu', 'home.home/kefu');
        // 友情链接
        Route::get('friend_links', 'home.home/friendLinks');
    });
});

// 搜索
Route::group('search', function () {
    // 搜索
    Route::group('search', function () {
        // 获取筛选列表
        Route::get('get_filter', 'search.search/getFilter');
        // 获取筛选商品列表
        Route::get('get_product', 'search.search/getProduct');
    });
    // 关键词搜索
    Route::group('search_guess', function () {
        // 获取关键词搜索列表
        Route::get('index', 'search.searchGuess/index');
    });
});

// 系统配置
Route::group('sys', function () {
    // 地区
    Route::group('region', function () {
        // 获取系统配置
        Route::get('get_region', 'sys.region/getRegion');
        // 获得所有省份接口
        Route::get('get_province_list', 'sys.region/getProvinceList');
        // 获得用户所在省份
        Route::get('get_user_region', 'sys.region/getUserRegion');
    });
});

// 会员中心
Route::group('user', function () {
    // 账户明细
    Route::group('account', function () {
        // 账户金额变动列表
        Route::get('list', 'user.account/list');
    });
    // 收货地址
    Route::group('address', function () {
        // 收货地址列表
        Route::get('list', 'user.address/list');
        // 收货地址详情
        Route::get('detail', 'user.address/detail');
        // 收货地址添加
        Route::post('create', 'user.address/create');
        // 收货地址更新
        Route::post('update', 'user.address/update');
        // 收货地址删除
        Route::post('del', 'user.address/del');
        // 设为选中
        Route::post('set_selected', 'user.address/setSelected');
    });
    // 售后
    Route::group('aftersales', function () {
        // 可售后订单列表
        Route::get('list', 'user.aftersales/list');
        // 配置型
        Route::get('config', 'user.aftersales/config');
        // 售后详情
        Route::get('apply_data', 'user.aftersales/applyData');
        // 售后申请
        Route::post('create', 'user.aftersales/create');
        // 售后申请记录
        Route::get('get_record', 'user.aftersales/getRecord');
        // 查看售后记录
        Route::get('detail', 'user.aftersales/detail');
        // 查看售后log记录
        Route::get('detail_log', 'user.aftersales/detailLog');
        // 提交售后反馈记录
        Route::post('feedback', 'user.aftersales/feedback');
        // 撤销申请售后
        Route::post('cancel', 'user.aftersales/cancel');
    });
    // 商品收藏
    Route::group('collect_product', function () {
        // 商品收藏列表
        Route::get('list', 'user.collectProduct/list');
        // 收藏商品
        Route::post('save', 'user.collectProduct/save');
        // 取消收藏
        Route::post('cancel', 'user.collectProduct/cancel');
    });
    // 评论晒单
    Route::group('comment', function () {
        // 评论晒单数量
        Route::get('sub_num', 'user.comment/subNum');
        // 晒单列表
        Route::get('showed_list', 'user.comment/showedList');
        // 已评价列表
        Route::get('list', 'user.comment/list');
        // 商品评价 / 晒单
        Route::post('evaluate', 'user.comment/evaluate');
        // 评价/晒单详情
        Route::get('detail', 'user.comment/detail');
    });
    // 优惠券
    Route::group('coupon', function () {
        // 会员优惠券列表
        Route::get('list', 'user.coupon/list');
        // 删除优惠券
        Route::post('del', 'user.coupon/del');
        // 优惠券列表
        Route::get('get_list', 'user.coupon/getList');
        // 领取优惠券
        Route::post('claim', 'user.coupon/claim');
        // 优惠券详情
        Route::get('detail', 'user.coupon/detail');
    });
    // 留言咨询
    Route::group('feedback', function () {
        // 订单咨询/留言列表
        Route::get('list', 'user.feedback/list');
        // 提交留言
        Route::post('submit', 'user.feedback/submit');
    });
    // 增票资质发票
    Route::group('invoice', function () {
        // 详情
        Route::get('detail', 'user.invoice/detail');
        // 添加
        Route::post('create', 'user.invoice/create');
        // 更新
        Route::post('update', 'user.invoice/update');
        // 判断当前用户的增票资质是否审核通过
        Route::get('get_status', 'user.invoice/getStatus');
    });
    // 登录
    Route::group('login', function () {
        // 登录
        Route::post('signin', 'user.login/signin');
        // 获取验证码
        Route::post('send_mobile_code', 'user.login/sendMobileCode');
        // 获得pc端微信登录跳转的url
        Route::get('get_wx_login_url', 'user.login/getWxLoginUrl');
        // 通过微信code获得微信用户信息
        Route::get('get_wx_login_info_by_code', 'user.login/getWxLoginInfoByCode');
    });
    // 站内信
    Route::group('message', function () {
        // 站内信列表
        Route::get('list', 'user.message/list');
        // 全部标记已读
        Route::post('update_all_read', 'user.message/updateAllRead');
        // 设置站内信已读
        Route::post('update_message_read', 'user.message/updateMessageRead');
        // 删除站内信
        Route::post('del', 'user.message/del');
    });

    // 订单
    Route::group('order', function () {
        // 列表
        Route::get('list', 'user.order/list');
        // 详情
        Route::get('detail', 'user.order/detail');
        // 数量
        Route::get('order_num', 'user.order/orderNum');
        // 取消
        Route::post('cancel_order', 'user.order/cancelOrder');
        // 删除
        Route::post('del_order', 'user.order/delOrder');
        // 收货
        Route::post('confirm_receipt', 'user.order/confirmReceipt');
        // 物流信息
        Route::get('shipping_info', 'user.order/shippingInfo');
        // 再次购买
        Route::post('buy_again', 'user.order/buyAgain');
    });
    // 订单发票
    Route::group('order_invoice', function () {
        //详情
        Route::get('detail', 'user.order_invoice/detail');
        // 新增
        Route::post('create', 'user.order_invoice/create');
        // 编辑
        Route::post('update', 'user.order_invoice/update');
    });
    // 积分
    Route::group('points_log', function () {
        // 列表
        Route::get('list', 'user.pointsLog/list');
    });
    // 充值
    Route::group('recharge_order', function () {
        // 列表
        Route::get('list', 'user.rechargeOrder/list');
        // 充值申请
        Route::post('update', 'user.rechargeOrder/update');
        // 充值金额列表
        Route::get('setting', 'user.rechargeOrder/setting');
        // 充值支付列表
        Route::get('payment_list', 'user.rechargeOrder/paymentList');
        // 充值支付
        Route::post('pay', 'user.rechargeOrder/pay');
        // 充值支付
        Route::post('create', 'user.rechargeOrder/create');
        // 获取充值支付状态
        Route::get('check_status', 'user.rechargeOrder/checkStatus');
    });
    // 会员登录
    Route::group('regist', function () {
        // 会员登录操作
        Route::post('regist_act', 'user.regist/registAct');
        // 验证码
        Route::post('send_mobile_code', 'user.regist/sendMobileCode');
    });
    // 会员
    Route::group('user', function () {
        // 会员详情
        Route::get('detail', 'user.user/detail');
        // 修改个人信息
        Route::post('update_information', 'user.user/updateInformation');
        // 会员中心首页数据
        Route::get('member_center', 'user.user/memberCenter');
        // 授权回调获取用户信息
        Route::post('oAuth', 'user.user/oAuth');
        // 修改密码获取验证码
        Route::post('send_mobile_code_by_modify_password', 'user.user/sendMobileCodeByModifyPassword');
        // 修改密码手机验证
        Route::post('check_modify_password_mobile_code', 'user.user/checkModifyPasswordMobileCode');
        // 修改密码
        Route::post('modify_password', 'user.user/modifyPassword');
        // 手机修改获取验证码
        Route::post('send_mobile_code_by_mobile_validate', 'user.user/sendMobileCodeByMobileValidate');
        // 手机修改新手机获取验证码
        Route::post('send_mobile_code_by_modify_mobile', 'user.user/sendMobileCodeByModifyMobile');
        // 手机绑定
        Route::post('modify_mobile', 'user.user/modifyMobile');
        // 手机验证
        Route::post('mobile_validate', 'user.user/mobileValidate');
        // 邮箱验证
        Route::post('email_validate', 'user.user/emailValidate');
        // 最近浏览
        Route::get('history_product', 'user.user/historyProduct');
        // 上传文件接口
        Route::post('upload_img', 'user.user/uploadImg');
        // 修改头像
        Route::post('modify_avatar', 'user.user/modifyAvatar');
    });
    // 提现
    Route::group('withdraw_apply', function () {
        // 列表
        Route::get('list', 'user.withdrawApply/list');
        // 添加提现账号
        Route::post('create_account', 'user.withdrawApply/createAccount');
        // 编辑提现账号
        Route::post('update_account', 'user.withdrawApply/updateAccount');
        // 提现账号详情
        Route::get('account_detail', 'user.withdrawApply/accountDetail');
        // 删除提现账号
        Route::post('del_account', 'user.withdrawApply/delAccount');
        // 提现申请
        Route::post('apply', 'user.withdrawApply/apply');
    });
});
