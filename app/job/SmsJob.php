<?php

namespace app\job;


use app\service\api\admin\common\sms\SmsService;

/**
 * 发送短信队列
 */
class SmsJob extends BaseJob
{

    /**
     * @param $data [mobile,template_code,content]
     * @return bool
     */
    public function doJob($data): bool
    {
        try {
            $smsService = new SmsService();
            if (empty($data['mobile'])) return false;
            $smsService->createSmsService()->sendSms($data['mobile'], $data['template_code'], $data['content']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}