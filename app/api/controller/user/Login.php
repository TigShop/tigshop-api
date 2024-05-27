<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 会员登录
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\api\controller\user;

use app\api\IndexBaseController;
use app\service\api\admin\captcha\CaptchaService;
use app\service\api\admin\common\sms\SmsService;
use app\service\api\admin\oauth\WechatOAuthService;
use app\service\api\admin\user\UserService;
use think\App;
use think\Response;

/**
 * 会员登录控制器
 */
class Login extends IndexBaseController
{
    /**
     * 构造函数
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * 会员登录操作
     *
     * @return Response
     */
    public function signin(): Response
    {
        $login_type = input('login_type', 'password');
        if ($login_type == 'password') {
            // 密码登录
            $username = input('username', '');
            $password = input('password', '');
            if (empty($username)) {
                return $this->error('用户名不能为空');
            }
            // 行为验证码
            app(CaptchaService::class)->setTag('userSignin:' . $username)
                ->setToken(input('verify_token', ''))
                ->setAllowNoCheckTimes(3) //3次内无需判断
                ->verification();
            $user = app(UserService::class)->getUserByPassword($username, $password);
        } elseif ($login_type == 'mobile') {
            // 手机登录
            $mobile = input('mobile', '');
            $mobile_code = input('mobile_code', '');
            $user = app(UserService::class)->getUserByMobile($mobile, $mobile_code);
        }
        if (!$user) {
            return $this->error('账户名或密码错误！');
        }
        app(UserService::class)->setLogin($user['user_id']);
        $token = app(UserService::class)->getLoginToken($user['user_id']);
        return $this->success([
            'token' => $token,
        ]);
    }
    /**
     * 获取验证码
     * @throws \exceptions\ApiException
     */
    public function sendMobileCode()
    {
        $mobile = input('mobile', '');
        if (!$mobile) {
            return $this->error('手机号不能为空');
        }
        // 行为验证码
        app(CaptchaService::class)->setTag('mobileCode:' . $mobile)
            ->setToken(input('verify_token', ''))
            ->verification();

        try {
            app(SmsService::class)->sendCode($mobile);
            return $this->success('发送成功！');
        } catch (\Exception $e) {
            return $this->error('发送失败！' . $e->getMessage());
        }
    }

    /**
     * 获得pc端微信登录跳转的url
     * @throws \exceptions\ApiException
     */
    public function getWxLoginUrl()
    {
        $url = input('url', '');
        if (!$url) {
            return $this->error('url不能为空');
        }
        $url = app(WechatOAuthService::class)->getQrOAuthUrl($url);
        return $this->success([
            'url' => $url,
        ]);
    }

    /**
     * 通过微信code获得微信用户信息
     * @throws \exceptions\ApiException
     */
    public function getWxLoginInfoByCode()
    {
        $code = input('code', '');
        if (!$code) {
            return $this->error('code不能为空');
        }
        $info = app(WechatOAuthService::class)->auth($code);
        return $this->success([
            'info' => $info,
        ]);
    }

}
