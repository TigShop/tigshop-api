<?php

namespace app\job\order;

use app\job\BaseJob;
use app\service\order\OrderService;

class OrderCancelJob extends BaseJob
{
    /**
     * 取消订单
     * @param array $data ['action' => 'cancelUnPayOrder', 'data' => ['order_id' => 1]]
     * @return bool
     */
    public function cancelUnPayOrder(array $data): bool
    {
        app(OrderService::class)->cancelOrder($data['data']['order_id']);
        return true;
    }
}