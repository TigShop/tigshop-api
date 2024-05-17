<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 会员等级
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\user;

use think\Validate;

class FeedbackValidate extends Validate
{
    protected $rule = [
        'content' => 'require',
        'email' => 'checkEmail',
    ];

    protected $message = [
        'content.require' => '内容不能为空',
        'email.checkEmail' => '邮箱格式不正确',
    ];

    // 邮箱验证
    public function checkEmail($value, $rule, $data = [], $field = '')
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}
