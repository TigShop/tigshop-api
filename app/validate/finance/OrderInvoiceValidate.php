<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 发票申请
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\finance;

use think\Validate;

class OrderInvoiceValidate extends Validate
{
    protected $rule = [
        'company_name' => 'require|max:100',
    ];

    protected $message = [
        'company_name.require' => '发票申请名称不能为空',
        'company_name.max' => '发票申请名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'company_name',
        ],
        'update' => [
            'company_name',
        ],
    ];
}
