<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 订单不同状态的操作
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\order;

use app\service\BaseService;
use app\service\pay\AliPayService;
use app\service\pay\WechatService;
use common\Exception;

/**
 * 订单服务类
 */
class PaymentService extends BaseService
{

    const PAY_TYPE_ONLINE = 1; //在线支付（微信、支付等）
    const PAY_TYPE_COD = 2; //货到付款（支持先发货，后付款）
    const PAY_TYPE_OFFLINE = 3; //线下支付（银行汇款）

    public function __construct()
    {
    }

    public function pay(string $payCode, array $order, string $openid = '')
    {
        try {
            switch ($payCode) {
                case 'alipay':
                    return app(AliPayService::class)->AliPay($order);
                //支付宝支付
                case 'alipay_app':
                    return app(AliPayService::class)->AppPay($order);
                //支付宝APP支付
                case 'wechat_pay_mini':
                    //小程序支付
                    return app(WechatService::class)->MiniPay($order);
                case 'wechat_pay_html5':
                    //微信公众号支付
                    return app(WechatService::class)->JsApiPay($order);
                case 'wechat_pay_native':
                    //微信扫码支付
                    return app(WechatService::class)->NativePay($order);
                case 'wechat_pay_app':
                    //APP微信支付
                    return app(WechatService::class)->AppPay($order);
            }
        } catch (Exception $exception) {

        }
    }

}
