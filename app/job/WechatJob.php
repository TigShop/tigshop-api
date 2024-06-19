<?php

namespace app\job;

use app\service\api\admin\oauth\WechatOAuthService;

class WechatJob extends BaseJob
{
    /**
     * 发送公众号消息
     * @param $data https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function doJob($data): bool
    {
        try {
            app(WechatOAuthService::class)->sendWechatTemplateMessage($data);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}