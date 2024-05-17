<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 会员
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\user;

use app\model\authority\AdminUser;
use app\model\user\User;
use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'username' => 'require|checkUnique|max:100',
        'email' => "checkEmail|checkUnique",
        'mobile' => "checkUnique",
    ];

    protected $message = [
        'username.require' => '会员名称不能为空',
        'username.max' => '会员名称最多100个字符',
        'username.checkUnique' => '会员名称已存在',
        'email.checkEmail' => '邮箱格式不正确',
        'email.checkUnique' => '邮箱已存在',
        'mobile.checkUnique' => '手机号已存在',
    ];

    // 验证唯一
    public function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['user_id']) ? $data['user_id'] : 0;
        $query = User::where('user_id', '<>', $id);
        switch ($field) {
            case "username":
                $query = $query->where('username', $value);
                $admin_query = AdminUser::where('username', $value);
                if ($admin_query->count() || $query->count()) {
                    return false;
                }
                break;
            case "email":
                $query = $query->where('email', $value);
                break;
            case "mobile":
                $query = $query->where('mobile', $value);
                break;
        }
        return $query->count() === 0;
    }

    // 邮箱验证
    public function checkEmail($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}
