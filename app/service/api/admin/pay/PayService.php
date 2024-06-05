<?php

namespace app\service\api\admin\pay;

use utils\Config;

abstract class PayService
{
    /**
     * 下单
     * @return mixed
     */
    abstract public function pay(array $order): array;

    /**
     * 退款
     * @return mixed
     */
    abstract public function refund(array $order): array;

    /**
     * 回调处理
     * @return bool
     */
    abstract public function notify(): array;

    /**
     * 退款回调
     * @return array
     */
    abstract public function refund_notify(): array;

    /**
     * 获取支付回调
     * @return string
     */
    public function getNotifyUrl(): string
    {
        return Config::get('pc_domain') . '/api/order/pay/notify';
    }

    /**
     * 获取退款通知地址
     * @return string
     */
    public function getRefundNotifyUrl(): string
    {
        return Config::get('pc_domain') . '/api/order/pay/refund_notify';
    }

    /**
     * 获取同步跳转地址
     * @return string
     */
    public function getReturnUrl(): string
    {
        return Config::get('pc_domain') . '/member/order/list';
    }
}
