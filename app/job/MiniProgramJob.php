<?php

namespace app\job;

use app\service\api\admin\oauth\WechatOAuthService;

class MiniProgramJob extends BaseJob
{
    /**
     * 发送订阅消息
     * @param $data https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/sendMessage.html
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function doJob($data): bool
    {
        try {
            app(WechatOAuthService::class)->sendMiniTemplateMessage($data);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}