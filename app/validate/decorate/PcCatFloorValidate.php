<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- PC分类抽屉
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\decorate;

use think\Validate;

class PcCatFloorValidate extends Validate
{
    protected $rule = [
        'category_ids' => "require",
    ];

    protected $message = [
        'category_ids.require' => '分类不能为空',
    ];

    protected $scene = [
        'create' => [
            'category_ids',
        ],
        'update' => [
            'category_ids',
        ],
    ];
}
