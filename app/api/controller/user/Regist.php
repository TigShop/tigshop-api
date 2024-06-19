<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 会员注册
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
use app\service\api\admin\user\UserRegistService;
use app\service\api\admin\user\UserService;
use exceptions\ApiException;
use think\App;
use utils\Config;

/**
 * 会员登录控制器
 */
class Regist extends IndexBaseController
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
     * @return \think\Response
     */
    public function registAct(): \think\Response
    {
        $data = $this->request->only([
            'regist_type' => 'mobile',
            'username' => '',
            'password' => '',
            'mobile' => '',
            'mobile_code' => '',
            'email' => '',
            'email_code' => '',
        ], 'post');
        $shop_reg_closed = Config::get('shop_reg_closed');
        if ($shop_reg_closed == 1) {
            $this->error('商城已停止注册！');
        }
        $regist_type = input('regist_type', 'mobile');
        $password = input('password', '');
        $referrer_user_id = input('referrer_user_id/d', 0);
        $username = app(UserRegistService::class)->generateUsername();
        if ($regist_type == 'mobile') {
            // 手机号注册
            $mobile = input('mobile', '');
            $mobile_code = input('mobile_code', '');
            if (empty($mobile)) {
                return $this->error('手机号不能为空');
            }
            if (empty($mobile_code)) {
                return $this->error('短信验证码不能为空');
            }
            if (app(SmsService::class)->checkCode($mobile, $mobile_code) == false) {
                // throw new ApiException('短信验证码错误或已过期，请重试');
            }
            $data = [
                'username' => $username,
                'password' => $password,
                'mobile' => $mobile,
                'referrer_user_id' => $referrer_user_id,
            ];
        } elseif ($regist_type == 'email') {
            // 邮箱注册
            $email = input('email', '');
            $email_code = input('email_code', '');
            if (empty($email)) {
                return $this->error('邮箱不能为空');
            }
            if (empty($email_code)) {
                return $this->error('邮箱验证码不能为空');
            }
            if (app(SmsService::class)->checkCode($email, $email_code) == false) {
                throw new ApiException('邮箱验证码错误或已过期，请重试');
            }
            $data = [
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'referrer_user_id' => $referrer_user_id,
            ];
        }

        try {
            $user = app(UserRegistService::class)->regist($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
        if (!$user) {
            return $this->error('注册失败');
        }
        // 设置登录状态
        app(UserService::class)->setLogin($user->user_id);

        $token = app(UserService::class)->getLoginToken($user->user_id);
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

}
