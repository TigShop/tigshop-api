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

namespace app\service\api\admin\user;

use app\service\api\admin\BaseService;
use exceptions\ApiException;

/**
 * 会员登录服务类
 */
class UserLoginService extends BaseService
{
    public function __construct()
    {
    }
    /**
     * 删除会员
     *
     * @param int $id
     * @return bool
     */
    public function login(string $username, string $password, $is_remember = null)
    {
        if (!$id) {
            throw new ApiException('#id错误');
        }
        return $result !== false;
    }
}
