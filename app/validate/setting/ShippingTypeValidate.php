<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 配送类型
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class ShippingTypeValidate extends Validate
{
    protected $rule = [
        'shipping_type_name' => 'require|max:100',
    ];

    protected $message = [
        'shipping_type_name.require' => '配送类型名称不能为空',
        'shipping_type_name.max' => '配送类型名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'shipping_type_name',
        ],
        'update' => [
            'shipping_type_name',
        ],
    ];
}
