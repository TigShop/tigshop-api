<?php

namespace app\service\api\admin\oauth;

use app\service\api\admin\BaseService;
use EasyWeChat\OfficialAccount\Application;
use exceptions\ApiException;
use think\Exception;
use utils\Config;
use utils\Util;

class WechatOAuthService extends BaseService
{
    protected string|null $platformType = null;

    /**
     *获取平台类型
     * @return string
     */
    public function getPlatformType(): string
    {
        if ($this->platformType === null) {
            return Util::getClientType();
        } else {
            return $this->platformType;
        }
    }

    /**
     * 设置平台类型
     * @param string $platformType
     * @return void
     */
    public function setPlatformType(string $platformType): self
    {
        $this->platformType = $platformType;
        return $this;
    }

    public function webpage_auth(string $code): array
    {
        $user = $this->getApplication()->getOAuth()->userFromCode($code);
        $user->getId();//对应微信的 openid
        $user->getNickname();//对应微信的 nickname
        $user->getName(); //对应微信的 nickname
        $user->getAvatar(); //头像地址
        $user->getRaw(); //原始 API 返回的结果
        $user->getAccessToken(); //access_token
        $user->getRefreshToken(); //refresh_token
        $user->getExpiresIn(); //expires_in，Access Token 过期时间
        $user->getTokenResponse(); //返回 access_token 时的响应值

        return [];
    }

    /**
     * 授权获取用户信息
     * @param string $code
     * @return array
     * @throws Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function auth(string $code): array
    {
        try {
            $user = $this->getApplication()->getOAuth()->userFromCode($code)->getRaw();
        } catch (Exception $exception) {
            throw new ApiException($exception->getMessage());
        }
        return $user;
    }

    /**
     * 获取网页授权地址
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getOAuthUrl(): string
    {
        switch ($this->getPlatformType()) {
            case 'wechat':
                $url = Config::get('h5_domain') . '/pages/login/index';
                $url = 'http://test.tigshop.com/api/user/login/get_wx_login_info_by_code';
                return $this->getApplication()->getOAuth()->scopes(['snsapi_userinfo'])->redirect($url);
            case 'pc':
                $url = Config::get('pc_domain') . '/member/login';
                return $this->getApplication()->getOAuth()->scopes(['snsapi_login'])->redirect($url);
            default:
                return '';
        }
    }

    /**
     * 发送公众号模板消息
     * @param array $data
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendWechatTemplateMessage(array $data = []): bool
    {
        try {
            $accessToken = $this->setPlatformType('wechat')->getApplication()->getAccessToken()->getToken();
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $accessToken;
            $response = $this->getApplication()->getClient()->postJson($url, $data);
            $response->toArray(false);
            return true;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            return false;
        }
    }

    /**
     * 发送小程序订阅消息
     * @param array $data
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function sendMiniTemplateMessage(array $data = []): bool
    {
        try {
            $accessToken = $this->setPlatformType('miniProgram')->getApplication()->getAccessToken()->getToken();
            $url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $accessToken;
            $response = $this->getApplication()->getClient()->postJson($url, $data);
            $response->toArray(false);
            return true;
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            return false;
        }
    }

    /**
     * 获取基础配置并返回application对象
     * @param string $type
     * @return object|Application
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getApplication(): object
    {
        $app_id = '';
        $secret = '';
        $callback = '';
        switch ($this->getPlatformType()) {
            case 'pc':
                $app_id = Config::get('lyecs_wechat_open_appId');
                $secret = Config::get('lyecs_wechat_open_appSecret');
                $callback = 'https://demo2.lyecs.com/member/login/wechat_scan_login';
                break;
            case 'wechat':
                $app_id = Config::get('lyecs_wechat_appId');
                $secret = Config::get('lyecs_wechat_appSecret');
                $callback = 'https://demo2.lyecs.com/member/login/wx_login';
                break;
            case 'miniProgram':
                $app_id = Config::get('lyecs_wechat_miniProgram_appId');
                $secret = Config::get('lyecs_wechat_miniProgram_secret');
                break;
            case 'app':
                $app_id = Config::get('lyecs_wechat_app_appId');
                $secret = Config::get('lyecs_wechat_app_secret');
                break;
        }
        $config = [
            'app_id' => $app_id,
            'secret' => $secret,
            'token' => 'easywechat',
            'aes_key' => '', // 明文模式请勿填写 EncodingAESKey
            'oauth' => [
                'scopes' => ['snsapi_userinfo'],
                'callback' => $callback,
            ],
            'http' => [
                'timeout' => 5.0,
                'retry' => true, // 使用默认重试配置
            ],
        ];

        return new Application($config);

    }


}