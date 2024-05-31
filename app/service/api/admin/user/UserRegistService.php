<?php
//**---------------------------------------------------------------------+
//** 服务层文件 -- 会员登录
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

declare (strict_types = 1);

namespace app\service\api\admin\user;

use app\model\user\User;
use app\service\api\admin\BaseService;
use app\service\api\admin\setting\ConfigService;
use exceptions\ApiException;
use utils\Config;
use utils\Time;

/**
 * 会员登录服务类
 */
class UserRegistService extends BaseService
{
    public function __construct()
    {
    }
    /**
     * 删除会员
     *
     * @param int $id
     * @return User
     */
    public function regist($params = []): User
    {
        if (!empty(Config::get('shop_reg_closed'))) {
            throw new ApiException('商城暂不开放注册！');
        }
        if (empty($params['username'])) {
            throw new ApiException('会员名称不能为空');
        }
        if ($this->checkUsernameRegisted($params['username']) === true) {
            throw new ApiException('该会员名称已被注册');
        }
        if (isset($params['mobile']) && $this->checkUserMobileRegisted($params['mobile']) === true) {
            throw new ApiException('该手机号已被注册');
        }
        if (isset($params['email']) && $this->checkUserEmailRegisted($params['email']) === true) {
            throw new ApiException('该邮箱已被注册');
        }
        $data = [
            'username' => $params['username'],
            'mobile' => $params['mobile'] ?? '',
            'email' => $params['email'] ?? '',
            'password' => password_hash($params['password'], PASSWORD_DEFAULT),
            'avatar' => isset($params['avatar']) ? $params['avatar'] : '',
            'nickname' => isset($params['nickname']) ? $params['nickname'] : '',
            'reg_time' => Time::now(),
            'referrer_user_id' => isset($params['referrer_user_id']) ? $params['referrer_user_id'] : 0, //推荐人
        ];
        $user = new User();
        $user->save($data);
        return $user;
    }
    /**
     * 检查会员名称是否已注册
     *
     * @param [string] $username
     * @return bool
     */
    public function checkUsernameRegisted(string $username): bool
    {
        $count = User::where('username', $username)->count();
        return $count > 0;
    }
    /**
     * 检查会员手机号是否已注册
     *
     * @param [string] $mobile
     * @return bool
     */
    public function checkUserMobileRegisted(string $mobile): bool
    {
        $count = User::where('mobile', $mobile)->count();
        return $count > 0;
    }
    /**
     * 检查会员邮箱是否已注册
     *
     * @param [string] $email
     * @return bool
     */
    public function checkUserEmailRegisted(string $email): bool
    {
        $count = User::where('email', $email)->count();
        return $count > 0;
    }

    /**
     * 生成用户名
     * @return string
     */
    public function generateUsername(): string
    {
        while (true) {
            $username = Config::get('username_prefix') . rand(100000, 999999);
            if (!User::where('username', $username)->exists()) {
                return $username;
            }
        }
    }

}
