<?php

namespace utils;

use think\facade\Queue;

class TigQueue
{

    /**
     * 加入延迟队列
     * 示例 app(TigQueue::class)->later(OrderCancelJob::class, 10, $data);
     * @param string $jobClassName
     * @param int $delay
     * @param array $data ['action' => 'cancelUnPayOrder', 'data' => ['order_id' => 1]]
     * @return mixed
     */
    public function later(string $jobClassName, int $delay = 0, array $data = []): mixed
    {
        return Queue::later($delay, $jobClassName, $data);
    }

    /**
     * 加入队列
     * 示例 --app(TigQueue::class)->push(OrderCancelJob::class, $data);
     * @param string $jobClassName
     * @param array $data ['action' => 'cancelUnPayOrder', 'data' => ['order_id' => 1]]
     * @return mixed
     */
    public function push(string $jobClassName, array $data = []): mixed
    {
        return Queue::push($jobClassName, $data);
    }

}