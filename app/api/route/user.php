<?php
use think\facade\Route;

// 会员中心
Route::group('user', function () {
    // 账户明细
    Route::group('account', function () {
        // 账户金额变动列表
        Route::get('list', 'user.account/list');
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
    // 商品收藏
    Route::group('collect_product', function () {
        // 商品收藏列表
        Route::get('list', 'user.collectProduct/list');
        // 收藏商品
        Route::post('save', 'user.collectProduct/save');
        // 取消收藏
        Route::post('cancel', 'user.collectProduct/cancel');
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
    // 留言咨询
    Route::group('feedback', function () {
        // 订单咨询/留言列表
        Route::get('list', 'user.feedback/list');
        // 提交留言
        Route::post('submit', 'user.feedback/submit');
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
    // 登录
    Route::group('login', function () {
        //快捷登录设置项目
        Route::get('get_quick_login_setting', 'user.login/getQuickLoginSetting');
        // 登录
        Route::post('signin', 'user.login/signin');
        // 获取验证码
        Route::post('send_mobile_code', 'user.login/sendMobileCode');
        // 获得pc端微信登录跳转的url
        Route::get('get_wx_login_url', 'user.login/getWechatLoginUrl');
        // 通过微信code获得微信用户信息
        Route::get('get_wx_login_info_by_code', 'user.login/getWechatLoginInfoByCode');
        //第三方绑定手机号
        Route::post('bind_mobile', 'user.login/bindMobile');
        //微信服务器校验
        Route::get('wechat_server', 'user.login/wechatServerVerify');
        //获取微信推送消息
        Route::post('wechat_server', 'user.login/getWechatMessage');
        //检测微信用户操作事件
        Route::post('wechat_event', 'user.login/wechatEvent');
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);

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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
    // 订单发票
    Route::group('order_invoice', function () {
        //详情
        Route::get('detail', 'user.order_invoice/detail');
        // 新增
        Route::post('create', 'user.order_invoice/create');
        // 编辑
        Route::post('update', 'user.order_invoice/update');
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
    // 积分
    Route::group('points_log', function () {
        // 列表
        Route::get('list', 'user.pointsLog/list');
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
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
    })->middleware([
        \app\api\middleware\JWT::class,
    ]);
});
