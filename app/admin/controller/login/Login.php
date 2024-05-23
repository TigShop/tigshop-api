<?php
//**---------------------------------------------------------------------+
//** 后台控制器文件 -- 登录
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\admin\controller\login;

use app\BaseController;
use app\service\api\admin\authority\AccessTokenService;
use app\service\api\admin\authority\AdminUserService;
use app\service\api\admin\captcha\CaptchaService;
use app\service\api\admin\common\sms\SmsService;
use think\App;
use think\Response;

/**
 * 品牌控制器
 */
class Login extends BaseController
{
    protected AdminUserService $adminUserService;
    private bool $isAdd = false;

    /**
     * 构造函数
     *
     * @param App $app
     * @param AdminUserService $brandService
     */
    public function __construct(App $app, AdminUserService $adminUserService)
    {
        parent::__construct($app);
        $this->adminUserService = $adminUserService;
    }

    /**
     * 管理员登录操作
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
            app(CaptchaService::class)->setTag('adminSignin:' . $username)
                ->setToken(input('verify_token', ''))
                ->setAllowNoCheckTimes(3) //3次内无需判断
                ->verification();
            $user = $this->adminUserService->getAdminUserByPassword($username, $password);
        } elseif ($login_type == 'mobile') {
            // 手机登录
            $mobile = $username = input('mobile', '');
            $mobile_code = input('mobile_code', '');
            $user = $this->adminUserService->getAdminUserByMobile($mobile, $mobile_code);
        }
        if (!$user) {
            return $this->error('账户或密码错误！');
        }
        $this->adminUserService->setLogin($user->admin_id);
        $token = app(AccessTokenService::class)->setApp('admin')->setId($user->admin_id)->createToken();

        return $this->success([
            'token' => $token,
        ]);
    }

    /**
     * 获取验证码
     * @return Response
     * @throws \exceptions\ApiException
     */
    public function sendMobileCode(): Response
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
