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
use app\service\api\admin\oauth\UserAuthorizeService;
use app\service\api\admin\oauth\WechatOAuthService;
use app\service\api\admin\user\UserRegistService;
use app\service\api\admin\user\UserService;
use Fastknife\Utils\RandomUtils;
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
            $user = app(UserService::class)->getUserByMobileCode($mobile, $mobile_code);
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
     * 获取微信登录跳转的url
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getWechatLoginUrl(): Response
    {
        $url = app(WechatOAuthService::class)->getOAuthUrl();
        return $this->success([
            'url' => $url,
        ]);
    }


    /**
     * 通过微信code获得微信用户信息
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    public function getWechatLoginInfoByCode(): Response
    {
        $code = input('code', '');
        if (!$code) {
            return $this->error('code不能为空');
        }
        $open_data = app(WechatOAuthService::class)->auth($code);
        if (isset($open_data['errcode'])) {
            return $this->error($open_data['errmsg']);
        }
        //检测用户是否已经绑定过账号，有则登录账号
        $openid = $open_data['openid'];
        $unionid = $open_data['unionid'] ?? '';
        $user_id = app(UserAuthorizeService::class)->getUserOAuthInfo($openid, $unionid);
        if (empty($user_id)) {
            return $this->success(['type' => 2, 'open_data' => $open_data]);
        }
        app(UserService::class)->setLogin($user_id);
        $token = app(UserService::class)->getLoginToken($user_id);
        return $this->success([
            'type' => 1,
            'token' => $token,
        ]);
    }

    /**
     * 绑定手机号
     * @return Response
     * @throws \exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bindMobile(): Response
    {
        $data = $this->request->only([
            'mobile' => '',
            'mobile_code' => '',
            'open_data' => [],
            'referrer_user_id/d' => 0,
        ], 'post');
        if (app(SmsService::class)->checkCode($data['mobile'], $data['mobile_code']) == false) {
            //return $this->error('短信验证码错误或已过期，请重试');
        }
        //检测手机号是否存在
        $user = app(UserService::class)->getUserByMobile($data['mobile']);
        if (empty($user)) {
            try {
                $username = app(UserRegistService::class)->generateUsername();
                //随机密码
                $password = RandomUtils::getRandomCode(8);
                $register = [
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $data['mobile'],
                    'referrer_user_id' => $data['referrer_user_id'],
                ];
                $user = app(UserRegistService::class)->regist($register);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
        }
        if (isset($data['open_data']['openid'])){
            app(UserAuthorizeService::class)->addUserAuthorizeInfo($user['user_id'], $data['open_data']['openid'] ?? '', $data['open_data'], $data['open_data']['unionid'] ?? '');
        }
        app(UserService::class)->setLogin($user['user_id']);
        $token = app(UserService::class)->getLoginToken($user['user_id']);
        return $this->success([
            'token' => $token,
        ]);
    }
}
