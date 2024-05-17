<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 商品服务
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\product;

use think\Validate;

class ProductServicesValidate extends Validate
{
    protected $rule = [
        'product_service_name' => 'require|max:100',
    ];

    protected $message = [
        'product_service_name.require' => '商品服务名称不能为空',
        'product_service_name.max' => '商品服务名称最多100个字符',
    ];
}
