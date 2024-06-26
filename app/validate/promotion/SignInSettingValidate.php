<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 积分签到
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\promotion;

use think\Validate;

class SignInSettingValidate extends Validate
{
    protected $rule = [
        'name' => 'require|max:100',
    ];

    protected $message = [
        'name.require' => '积分签到名称不能为空',
        'name.max' => '积分签到名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'name',
        ],
        'update' => [
            'name',
        ],
    ];
}
