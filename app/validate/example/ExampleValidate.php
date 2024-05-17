<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 示例模板
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\example;

use think\Validate;

class ExampleValidate extends Validate
{
    protected $rule = [
        'example_id' => 'require',
        'example_name' => 'require|max:10',
        'example_others' => 'checkOther',
    ];

    protected $message = [
        'example_id.require' => 'id不能为空',
        'example_name.require' => '示例模板名称不能为空',
        'example_name.max' => '示例模板名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'example_name',
            'example_others',
        ],
        'update' => [
            'example_id',
            'example_name',
            'example_others',
        ],
        'delete' => [
            'example_id',
        ],
    ];

    protected function checkOther($vale, $rule, $data = []): bool
    {
        //特殊要求的校验

        return true;
    }
}
