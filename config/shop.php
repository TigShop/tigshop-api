<?php

// +----------------------------------------------------------------------
// | 商城设置项，后台新增字段时需在此处同步添加字段和默认值（此处修改值无效，请通过后台修改）
// +----------------------------------------------------------------------
return [
    // 基础设置项
    'base' => [
        'shop_name' => '',
        'shop_title' => '',
        'shop_title_suffix' => '',
        'shop_keywords' => '',
        'shop_desc' => '',
        'shop_logo' => '',
        'default_avatar' => '',
        'ico_img' => '',
        'pc_domain' => '',
        'h5_domain' => '',
        'is_open_goods_cache' => 1,
        'is_open_cat_cache' => 1,
        'is_open_data_cache' => 1,
        'is_display_error' => 1,
        'style_version' => 3,
        'visit_stats' => 1,
        'is_buy_lymobile' => 1,
        'is_open_redis' => 0,
        'session_open_redis' => 0,
        'redis_host' => '127.0.0.1',
        'redis_host_port' => 6379,
        'redis_host_password' => '',
        'is_open_queue' => 0,
        'mobile_login_need_reg' => 0,
        'enable_mobile_logn' => 1,
        'is_wx_affiliate' => 1,
        'is_wx_auto_regist' => 1,
        'captcha_login_fail' => 1,
        'comment_check' => 0,
        'message_check' => 0,
        'shop_company' => '',
        'kefu_address' => '',
        'shop_icp_no' => '',
        'shop_icp_no_url' => '',
        'shop_110_no' => '',
        'shop_110_link' => '',
        'close_shop' => 0,
        'close_shop_reason' => '',
        'shop_reg_closed' => 0,
        'dollar_sign' => '¥',
        'dollar_sign_cn' => '元',
        'sn_prefix' => 'SN',
        'price_format' => 1,
        'default_storage' => 1,
        'goods_url_type' => 0,
        'limit_day' => 7,
        'is_auto_goods_keywords' => 1,
        'watermark' => '',
        'watermark_place' => 3,
        'watermark_alpha' => '',
        'is_show_price_trend' => 1,
        'show_selled_count' => 1,
        'show_marketprice' => 1,
        'is_spe_goods_number' => 1,
        'spe_goods_number_1' => 10,
        'spe_goods_number_2' => 30,
        'spe_goods_number_3' => 50,
        'page_size' => 20,
        'history_number' => 20,
        'ly_brand_type' => '',
        'comment_default_tag' => '',
        'market_price_rate' => 1.2,
        'use_storage' => 1,
        'stock_dec_time' => 1,
        'auto_split_paid_order' => 0,
        'child_area_need_region' => 0,
        'shipping_tpl_fee_merge' => 2,
        'auto_cancel_order_hour' => 8,
        'auto_cancel_order_minute' => 15,
        'recover_cancel_order' => 1,
        'use_bonus' => 1,
        'use_surplus' => 1,
        'use_integral' => 1,
        'integral_name' => '积分',
        'integral_scale' => 1,
        'integral_percent' => 50,
        'comment_send_point' => 5,
        'show_send_point' => 5,
        'use_qiandao_point' => 1,
        'can_invoice' => 1,
        'invoice_content' => '',
        'invoice_added' => 1,
        'lottery_closed' => 0,
        'lottery_point' => 5,
        'is_open_pin' => 1,
        'is_open_bargain' => 1,
        'return_consignee' => '',
        'return_mobile' => '',
        'return_address' => '',
        'sms_key_id' => '',
        'sms_key_secret' => '',
        'sms_sign_name' => '',
        'sms_shop_mobile' => '',
        'service_email' => '',
        'send_confirm_email' => 0,
        'order_pay_email' => 1,
        'send_service_email' => 1,
        'send_ship_email' => 0,
        'search_keywords' => '',
        'msg_hack_word' => 'http,link,请填入非法关键词',
        'is_open_pscws' => '',
        'self_store_name' => '官方自营',
        'shop_default_regions' => [],
        'default_country' => 1,
        'show_cat_level' => 0,
        'is_show_cat_icos' => 1,
        'banner_height' => 400,
        'is_show_home_qgmod' => 1,
        'is_show_home_bonus' => 1,
        'is_show_group' => 1,
        'is_show_global_imported' => 1,
        'index_new_limit' => 30,
        'tool_bar_color' => '',
        'kefu_type' => 1,
        'kefu_yzf_type' => 1,
        'kefu_yzf_sign' => '',
        'kefu_workwx_id' => '',
        'kefu_code' => '',
        'kefu_code_blank' => 3,
        'kefu_javascript' => '',
        'wap_kefu_javascript' => '',
        'kefu_phone' => '',
        'kefu_time' => '',
        'lyecs_wechat_appId' => '',
        'lyecs_wechat_appSecret' => '',
        'lyecs_wechat_open_appId' => '',
        'lyecs_wechat_open_appSecret' => '',
        'lyecs_wechat_pay_mchid_type' => '',
        'lyecs_wechat_pay_mchid' => '',
        'lyecs_wechat_pay_sub_mchid' => '',
        'lyecs_wechat_pay_key' => '',
        'lyecs_wechat_miniProgram_appId' => '',
        'lyecs_wechat_miniProgram_secret' => '',
        'wechat_pay_app_id' => '',
        'wechat_pay_app_secret' => '',
        'ico_tig_css' => '',
        'ico_defined_css' => '',
        'qq_login_key' => '',
        'qq_login_secret' => '',
        'lyecs_api_key' => '',
        'kuaidi100_limit' => 1,
        'kuaidi100_key' => '',
        'storage_type' => 1,
        'storage_local_url' => '',
        'storage_oss_url' => '',
        'storage_oss_access_key_id' => '',
        'storage_oss_access_key_secret' => '',
        'storage_oss_bucket' => '',
        'storage_oss_region' => '',
        'storage_cos_url' => '',
        'storage_cos_secret_id' => '',
        'storage_cos_secret_key' => '',
        'storage_cos_bucket' => '',
        'storage_cos_region' => '',
        'onebound_key' => '',
        'onebound_secret' => '',
    ],
    // 支付相关
    'payment' => [
        'use_surplus' => 1,
        'use_cod' => 1,
        'use_points' => 1,
        'use_coupon' => 1,
        // 微信支付
        'use_wechat' => 1,
        'wechat_mchid_type' => 1,
        'wechat_pay_mchid' => '',
        'wechat_pay_sub_mchid' => '',
        'wechat_pay_key' => '',
        'wechat_pay_serial_no' => '',
        'wechat_pay_private_key' => 0,
        'wechat_pay_certificate' => 0,
        'wechat_pay_platform_certificate' => 0,
        // 支付宝支付
        'use_alipay' => 1,
        'alipay_appid' => '',
        'alipay_rsa_private_key' => '',
        'alipay_rsa_public_key' => '',
        'alipay_rsa_sign_type' => 0,
        'alipay_rsa_sign_type_value' => 'RSA2',
        'alipay_rsa_sign_type_value_list' => 'RSA2',
        // payPal支付
        'payPal' => [
            'use_paypal' => 1,
            'paypal_client_id' => 0,
            'paypal_client_secret' => 0,
            'paypal_mode' => 0,
        ],
        // 线下支付
        'use_offline' => 1,
        'offline_pay_bank' => '',
        'offline_pay_company' => '',
    ],
    //商户的设置
    'merchant' => [
        'person_apply_enabled' => 1,
        'shop_product_need_check' => 0,
        'max_recommend_product_count' => '',
        'shop_rank_date_rage' => '',
        'enabled_commission_order' => '',
        'shop_agreement' => '',
        'merchant_apply_need_check' => 1,
        'default_admin_prefix' => ''
    ]
];
