<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- APP版本管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class AppVersionValidate extends Validate
{
    protected $rule = [
        '' => 'require|max:100',
    ];

    protected $message = [
        '.require' => 'APP版本管理名称不能为空',
        '.max' => 'APP版本管理名称最多100个字符',
    ];
}
