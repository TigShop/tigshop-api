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
use JetBrains\PhpStorm\NoReturn;
use think\App;
use think\facade\Cache;
use think\Response;
use utils\Config;
use utils\Util;

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
     * 获取快捷登录的选项--目前只有微信快捷登录
     * @return Response
     */
    public function getQuickLoginSetting(): Response
    {
        $wechat_login = 0;
        switch (Util::getClientType()) {
            case 'pc':
                $wechat_login = Config::get("wechat_open_scan");
                break;
            case 'wechat':
                $wechat_login = Config::get("wechat_oauth");
                break;
            case 'miniProgram':
                $wechat_login = 1;
                break;
            default:
                break;
        }
        $show_oauth = $wechat_login ? 1 : 0;
        return $this->success([
            'wechat_login' => $wechat_login,
            'show_oauth' => $show_oauth,
        ]);
    }

    /**
     * 会员登录操作
     * @return Response
     * @throws \exceptions\ApiException
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
        $res = app(WechatOAuthService::class)->getOAuthUrl();
        return $this->success([
            'url' => $res['url'],
            'ticket' => $res['ticket'] ?? '',
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
            'password' => '',
            'open_data' => [],
            'referrer_user_id/d' => 0,
        ], 'post');
        if (app(SmsService::class)->checkCode($data['mobile'], $data['mobile_code']) == false) {
            //return $this->error('短信验证码错误或已过期，请重试');
        }
        //检测手机号是否存在
        $user = app(UserService::class)->getUserByMobile($data['mobile']);
        if (empty($user)) {
            $shop_reg_closed = Config::get('shop_reg_closed');
            if ($shop_reg_closed == 1){
                $this->error('商城已停止注册！');
            }
            try {
                $username = app(UserRegistService::class)->generateUsername();
                $password = $data['password'] ?? RandomUtils::getRandomCode(8);
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
        if (isset($data['open_data']['openid'])) {
            app(UserAuthorizeService::class)->addUserAuthorizeInfo($user['user_id'], $data['open_data']['openid'] ?? '', $data['open_data'], $data['open_data']['unionid'] ?? '');
        }
        app(UserService::class)->setLogin($user['user_id']);
        $token = app(UserService::class)->getLoginToken($user['user_id']);
        return $this->success([
            'token' => $token,
        ]);
    }

    /**
     * 服务端验证
     * @return void
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    #[NoReturn] public function wechatServerVerify(): void
    {
        $body = app(WechatOAuthService::class)->setPlatformType('wechat')->getApplication()->getServer()->serve()->getBody();
        exit($body);
    }

    /**
     * 处理消息
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function getWechatMessage(): Response
    {
        $message = app(WechatOAuthService::class)->setPlatformType('wechat')->getApplication()->getServer()->getRequestMessage();
        file_put_contents('msg.txt', $message, FILE_APPEND);
        if (isset($message['Event'])) {
            //检测用户是否登录
            $openid = $message['FromUserName'];
            $ticket = $message['Ticket'];
            if (in_array($message['Event'], ['subscribe', 'SCAN'])) {
                if (!empty($ticket) && !empty($openid))
                Cache::set($ticket, $openid);
            }
        }

        return $this->success('Success');
    }

    /**
     * 检测用户扫码后处理事件
     * @return Response
     * @throws \exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function wechatEvent(): Response
    {
        $key = input('key');
        if (empty($key)) {
            return $this->success([
                'type' => 0,
                'message' => '未登录'
            ]);
        }
        $openid = Cache::get($key);
        if (empty($openid)) {
            return $this->success([
                'type' => 0,
                'message' => '未登录',
            ]);
        }
        $user_id = app(UserAuthorizeService::class)->getUserOAuthInfo($openid);
        $open_data = ['openid' => $openid];
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
}
