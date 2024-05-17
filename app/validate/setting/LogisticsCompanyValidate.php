<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 物流公司
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\setting;

use think\Validate;

class LogisticsCompanyValidate extends Validate
{
    protected $rule = [
        'logistics_name' => 'require|max:100',
    ];

    protected $message = [
        'logistics_name.require' => '物流公司名称不能为空',
        'logistics_name.max' => '物流公司名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'logistics_name',
        ],
        'update' => [
            'logistics_name',
        ],
    ];
}
