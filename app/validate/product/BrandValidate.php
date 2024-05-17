<?php

namespace app\validate\product;

use think\Validate;

class BrandValidate extends Validate
{
    protected $rule = [
        'brand_name' => 'require|checkUnique|max:30',
    ];

    protected $message = [
        'brand_name.require' => '品牌名称不能为空',
        'brand_name.checkUnique' => '品牌名称已存在',
        'brand_name.max' => '品牌名称最多30个字符',
    ];
    // 验证唯一
    protected function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['brand_id']) ? $data['brand_id'] : 0;
        $query = \think\facade\Db::name('brand')->where('brand_name', $value)->where('brand_id', '<>', $id);
        return $query->count() === 0;
    }
}
