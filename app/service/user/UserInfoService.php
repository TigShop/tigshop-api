<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 会员
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\service\user;

use app\common\exceptions\ApiException;
use app\common\utils\Time;
use app\common\utils\Util;
use app\model\order\Order;
use app\model\user\User;
use app\model\user\UserCoupon;
use app\service\BaseService;
use app\service\common\sms\SmsService;

/**
 * 会员服务类
 */
class UserInfoService extends BaseService
{
    protected int $id;

    public function __construct(int $user_id)
    {
        $this->id = $user_id;
    }

    /**1
     * @param int $id
     * @return object
     * @throws ApiException
     * 用户中心首页数据集合
     */
    public function getUserIndex(): object
    {
        $result = User::with(['userRank' => function ($query) {
            $query->field('rank_id, rank_name, rank_ico');
        }])
            ->field('user_id,username,nickname,avatar,rank_id,balance,points,mobile_validated,email_validated,is_svip')->append(['dim_username'])->find($this->id);

        if (!$result) {
            throw new ApiException('会员不存在');
        }

        if ($result->mobile_validated && $result->email_validated) {
            $result->security_lv = 3;
        } elseif (($result->mobile_validated && !$result->email_validated) || (!$result->mobile_validated && $result->email_validated)) {
            $result->security_lv = 2;
        } else {
            $result->security_lv = 1;
        }

        $result->await_pay = Order::where('user_id', $this->id)->awaitPay()->where("is_del",0)->count();
        $result->await_shipping = Order::where('user_id', $this->id)->awaitShip()->where("is_del",0)->count();
        $result->await_comment = Order::where('user_id', $this->id)->awaitComment()->where("is_del",0)->count();
        $result->await_coupon = app(UserCouponService::class)->getUserNormalCouponCount($this->id);

        return $result;
    }

    /**
     * 获取简单的详情详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getSimpleBaseInfo(): array
    {
        $result = User::field('user_id,username,nickname,avatar,points,balance,frozen_balance,birthday,mobile,email,history_product_ids')
            ->append(['dim_username'])->find($this->id);

        if (!$result) {
            throw new ApiException('会员不存在');
        }
        return $result->toArray();
    }

    /**
     * 获取详情
     *
     * @param int $id
     * @return array
     * @throws ApiException
     */
    public function getBaseInfo(): array
    {
        $result = User::with(['userRank'])->field('user_id,username,nickname,avatar,points,balance,frozen_balance,birthday,mobile,email,rank_id')
            ->append(['dim_username'])->find($this->id);

        if (!$result) {
            throw new ApiException('会员不存在');
        }
        $result->total_balance = Util::number_format_convert($result->balance + $result->frozen_balance);
        $result->avatar = app(UserService::class)->getUserAvatar($result->avatar);
        $result->coupon = app(UserCouponService::class)->getUserNormalCouponCount($this->id);

        return $result->toArray();
    }

    /**
     * 修改个人信息
     * @param array $data
     * @return bool
     * @throws ApiException
     */
    public function updateInformation(array $data)
    {
        $user = User::findOrEmpty($this->id);
        if (empty($user->toArray())) {
            throw new ApiException(/** LANG */'会员不存在');
        }
        if ($user->save($data)) {
            return true;
        }
        return false;
    }

    /**
     * 修改密码 / 支付密码
     * @param array $data
     * @return bool
     * @throws ApiException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function modifyPassword(array $data): bool
    {
        $user = User::find($this->id);

        if (empty($user)) {
            throw new ApiException(/** LANG */'会员不存在');
        }

        // 用户密码
        $result = $user->save(['password' => password_hash($data["password"], PASSWORD_DEFAULT)]);

        return $result !== false;
    }

    /**
     * 手机验证 / 手机绑定
     * @param string $mobile
     * @param int $code
     * @return bool
     * @throws ApiException
     */
    public function mobileValidate(string $mobile, int $code, int $type, string $event): bool
    {
        if (empty($mobile)) {
            throw new ApiException(/** LANG */'手机号不能为空');
        }
        if (empty($code)) {
            throw new ApiException(/** LANG */'请输入验证码');
        }
        if (app(SmsService::class)->checkCode($mobile, $code, $event) == false) {
            throw new ApiException(/** LANG */'短信验证码错误或已过期，请重试');
        }
        // 绑定手机
        if (User::where("mobile", $mobile)->where("user_id", "<>", $this->id)->count()) {
            throw new ApiException(/** LANG */'该手机号已被其他会员绑定,请更换手机号,或联系客服申诉');
        }

        if ($type) {
            User::find($this->id)->save(['mobile' => $mobile, 'mobile_validated' => 1]);
        }

        return true;
    }

    /**
     * 邮箱验证 / 邮箱绑定
     * @param string $email
     * @param int $type
     * @return true
     * @throws ApiException
     */
    public function emailValidate(string $email, int $type)
    {
        if (empty($email)) {
            throw new ApiException(/** LANG */'邮箱不能为空');
        }
        // 绑定邮箱
        if (User::where("email", $email)->where("user_id", "<>", $this->id)->count()) {
            throw new ApiException(/** LANG */'该邮箱已被其他会员绑定,请更换邮箱,或联系客服申诉');
        }
        if ($type) {
            User::find($this->id)->save(['email' => $email, 'email_validated' => 1]);
        }
        return true;
    }

    /**
     * 修改头像
     * @return bool
     * @throws ApiException
     */
    public function modify_avatar(string $avatar): bool
    {
        return User::find($this->id)->save(['avatar' => $avatar]);
    }

}
