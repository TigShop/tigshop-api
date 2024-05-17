<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 积分商品
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\promotion;

use think\facade\Db;
use think\Validate;

class PointsExchangeValidate extends Validate
{
    protected $rule = [
        "product_id" => "checkUnique",
    ];

    protected $message = [
        'product_id.checkUnique' => '已存在相同的积分商品',
    ];

    protected $scene = [
        'create' => [
            'product_id',
        ],
        'update' => [
            'product_id',
        ],
    ];


    // 验证唯一
    public function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['id']) ? $data['id'] : 0;
        $query = Db::name('points_exchange')->where('product_id', $value)->where('id', '<>', $id);
        return $query->count() === 0;
    }
}
