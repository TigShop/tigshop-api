<?php

namespace app\service\api\admin\pay;

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

}
