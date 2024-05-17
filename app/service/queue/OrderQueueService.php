<?php

namespace app\service\queue;

use app\common\utils\Config;
use app\common\utils\TigQueue;
use app\job\order\OrderCancelJob;
use app\service\BaseService;

class OrderQueueService extends BaseService
{
    /**
     * 取消订单队列
     * @param int $order_id
     * @return void
     */
    public function cancelUnPayOrderJob(int $order_id): void
    {
        $auto_cancel_order_minute = Config::get('auto_cancel_order_minute');
        if ($auto_cancel_order_minute > 0) {
            $job_data = ['action' => 'cancelUnPayOrder', 'data' => ['order_id' => $order_id]];
            app(TigQueue::class)->later(OrderCancelJob::class, $auto_cancel_order_minute * 60, $job_data);
        }
    }
}