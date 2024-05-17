<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 秒杀活动
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\promotion;

use think\Validate;

class SeckillValidate extends Validate
{
    protected $rule = [
        'seckill_name' => 'require|max:100',
    ];

    protected $message = [
        'seckill_name.require' => '秒杀活动名称不能为空',
        'seckill_name.max' => '秒杀活动名称最多100个字符',
    ];

    protected $scene = [
        'create' => [
            'seckill_name',
        ],
        'update' => [
            'seckill_name',
        ],
    ];
}
