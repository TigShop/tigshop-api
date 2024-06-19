<?php
//**---------------------------------------------------------------------+
//** 通用接口控制器文件 -- 会员信息
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
use app\service\api\admin\image\Image;
use app\service\api\admin\oauth\WechatOauthService;
use app\service\api\admin\product\ProductService;
use app\service\api\admin\user\UserInfoService;
use exceptions\ApiException;
use think\App;
use utils\Config;

/**
 * 会员中心控制器
 */
class User extends IndexBaseController
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
     * 会员基础信息
     * @param int $id
     * @return \think\Response
     */
    public function detail(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $data = $userInfoService->getBaseInfo();

        return $this->success(['item' => $data]);
    }

    /**
     * 修改个人信息
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function updateInformation(): \think\Response
    {
        $data = $this->request->only([
            'birthday' => '',
            "nickname" => "",
            "email" => "",
            "mobile" => "",
        ], 'post');
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->updateInformation($data);
        return $result ? $this->success(/** LANG */"修改成功") : $this->error(/** LANG */"修改失败");
    }

    /**
     * 会员中心首页数据
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function memberCenter(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $data = $userInfoService->getUserIndex();

        return $this->success(['item' => $data]);
    }
    /**
     * 授权回调获取用户信息
     * @return \think\Response
     */
    public function oAuth(): \think\Response
    {
        $code = input('code');
        $type = input('type');
        if (empty($type) || empty($code)) {
            return $this->error('参数缺失！');
        }

        switch ($type) {
            case 'wechat':
                $data = app(WechatOauthService::class)->auth($code);
                break;
            default:
                return $this->error('未找到授权类型！');
        }

        return $this->success(['data' => $data]);
    }

    /**
     * 修改密码获取验证码
     * @throws \exceptions\ApiException
     */
    public function sendMobileCodeByModifyPassword(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $userInfo = $userInfoService->getSimpleBaseInfo();
        $mobile = $userInfo['mobile'];
        $event = 'modify_password';
        // 行为验证码
        app(CaptchaService::class)->setTag($event . 'mobileCode:' . $mobile)
            ->setToken(input('verify_token', ''))
            ->verification();

        try {
            app(SmsService::class)->sendCode($mobile, $event);
            return $this->success('发送成功！');
        } catch (\Exception $e) {
            return $this->error('发送失败！' . $e->getMessage());
        }
    }

    /**
     * 修改密码手机验证
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function checkModifyPasswordMobileCode(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $userInfo = $userInfoService->getSimpleBaseInfo();
        $mobile = $userInfo['mobile'];
        $code = input("code", "");
        $password = input("password", "");
        if (empty($password)) {
            throw new ApiException(/** LANG */'新密码不能为空');
        }
        $userInfoService = new UserInfoService(request()->userId);
        $userInfoService->mobileValidate($mobile, $code, 0, 'modify_password');
        $result = $userInfoService->modifyPassword(['password' => $password]);
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

    /**
     * 修改密码
     * @return \think\Response
     * @throws \exceptions\ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function modifyPassword(): \think\Response
    {
        $data = $this->request->only([
            'old_password' => '',
            'password' => '',
            'confirm_password' => '',
        ], 'post');
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->modifyPassword($data);
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

    /**
     * 手机修改获取验证码
     * @throws \exceptions\ApiException
     */
    public function sendMobileCodeByMobileValidate(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $userInfo = $userInfoService->getSimpleBaseInfo();
        $mobile = $userInfo['mobile'];
        $event = 'mobile_validate';
        // 行为验证码
        app(CaptchaService::class)->setTag($event . 'mobileCode:' . $mobile)
            ->setToken(input('verify_token', ''))
            ->verification();

        try {
            app(SmsService::class)->sendCode($mobile, $event);
            return $this->success('发送成功！');
        } catch (\Exception $e) {
            return $this->error('发送失败！' . $e->getMessage());
        }
    }

    /**
     * 手机修改新手机获取验证码
     * @throws \exceptions\ApiException
     */
    public function sendMobileCodeByModifyMobile(): \think\Response
    {
        $mobile = input('mobile', '');
        if (!$mobile) {
            return $this->error('手机号不能为空');
        }
        $event = 'modify_mobile';
        // 行为验证码
        app(CaptchaService::class)->setTag($event . 'mobileCode:' . $mobile)
            ->setToken(input('verify_token', ''))
            ->verification();

        try {
            app(SmsService::class)->sendCode($mobile, $event);
            return $this->success('发送成功！');
        } catch (\Exception $e) {
            return $this->error('发送失败！' . $e->getMessage());
        }
    }

    /**
     * 手机验证
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function mobileValidate(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $userInfo = $userInfoService->getSimpleBaseInfo();
        $mobile = $userInfo['mobile'];
        $code = input("code", "");
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->mobileValidate($mobile, $code, 0, 'mobile_validate');
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

    /**
     * 手机绑定
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function modifyMobile(): \think\Response
    {
        $mobile = input("mobile", "");
        $code = input("code", "");
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->mobileValidate($mobile, $code, 1, 'modify_mobile');
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

    /**
     * 邮箱验证 / 邮箱绑定
     * @return \think\Response
     * @throws \exceptions\ApiException
     */
    public function emailValidate(): \think\Response
    {
        $email = input("email", "");
        $type = input("type/d", 0);
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->emailValidate($email, $type);
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

    /**
     * 最近浏览
     * @return \think\Response
     */
    public function historyProduct(): \think\Response
    {
        $userInfoService = new UserInfoService(request()->userId);
        $userInfo = $userInfoService->getSimpleBaseInfo();
        $list = [];
        if (!empty($userInfo['history_product_ids'])) {
            $history_product_ids = json_decode($userInfo['history_product_ids'], true);
            $list = app(ProductService::class)->getFilterResult([
                'product_ids' => $history_product_ids,
                'size' => 20,
                'sort_field_raw' => "field(product_id," . implode(',', $history_product_ids) . ")",
            ]);
        }
        return $this->success(['list' => $list]);
    }

    /**
     * pc端上传文件接口
     * @return \think\Response
     * @throws ApiException
     * @throws \think\Exception
     */
    public function uploadImg(): \think\Response
    {
        if (request()->file('file')) {
            $image = new Image(request()->file('file'), 'pc');
            $original_img = $image->save();
            $thumb_img = $image->makeThumb(200, 200);
        } else {
            return $this->error('图片上传错误！');
        }
        if (!$original_img || !$thumb_img) {
            return $this->error('图片上传错误！');
        }
        return $this->success([
            'pic_thumb' => $thumb_img,
            'pic_url' => $original_img,
            'pic_name' => $image->orgName,
            'storage_url' => $image->getStorageUrl(),
        ]);
    }

    /**
     * 修改头像
     * @return \think\Response
     * @throws ApiException
     * @throws \think\Exception
     */
    public function modifyAvatar(): \think\Response
    {
        if (request()->file('file')) {
            $image = new Image(request()->file('file'), 'gallery');
            $original_img = $image->save();
            $thumb_img = $image->makeThumb(200, 200);
        } else {
            return $this->error('图片上传错误！');
        }
        if (!$original_img || !$thumb_img) {
            return $this->error('图片上传错误！');
        }
        $userInfoService = new UserInfoService(request()->userId);
        $result = $userInfoService->modify_avatar(Config::get('') . $thumb_img);
        return $result ? $this->success(/** LANG */"操作成功") : $this->error(/** LANG */"操作失败");
    }

}
