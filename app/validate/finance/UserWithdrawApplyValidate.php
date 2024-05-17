<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 提现申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\finance;

use think\Validate;

class UserWithdrawApplyValidate extends Validate
{
    protected $rule = [
        'postscript' => 'max:80',
    ];

    protected $message = [
        'postscript.max' => '管理员备注最多80个字符',
    ];

    protected $scene = [
        'create' => [
            'postscript',
        ],
        'update' => [
            'postscript',
        ],
    ];
}
