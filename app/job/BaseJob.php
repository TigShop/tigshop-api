<?php

namespace app\job;

use think\Exception;
use think\queue\Job;

class BaseJob
{
    /**
     *自动调用
     * @param $name
     * @param $arguments
     * @return void
     */
    public function __call($name, $arguments)
    {
        $this->fire(...$arguments);
    }

    /**
     * 队列通用通道
     * @param Job $job
     * @param array $data
     * @return void
     */
    public function fire(Job $job, array $data): void
    {
        try {
            $action = $data['action'] ?? 'doJob';
            $attempted = $data['attempted'] ?? 0;//最大执行次数
            $this->handle($action, $job, $data, $attempted);
        } catch (Exception $exception) {
            $job->delete();
        }
    }

    /**
     *
     * @param string $action
     * @param Job $job
     * @param array $data
     * @param int $attempted
     * @return void
     */
    public function handle(string $action, Job $job, array $data, int $attempted = 3): void
    {
        if (!method_exists($this, $action)) {
            $job->delete();
        }
        if ($this->{$action}($data)) {
            $job->delete();
        } else {
            if ($job->attempts() >= $attempted && $attempted) {
                $job->delete();
            } else {
                $job->release();
            }
        }
    }
}