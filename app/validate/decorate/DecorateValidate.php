<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 装修
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\decorate;

use think\Validate;

class DecorateValidate extends Validate
{
    protected $rule = [
        'decorate_title' => 'require|max:100',
    ];

    protected $message = [
        'decorate_title.require' => '装修名称不能为空',
        'decorate_title.max' => '装修名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'decorate_title',
        ],
        'update' => [
            'decorate_title',
        ],
    ];
}
