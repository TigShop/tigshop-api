<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 商品管理
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\product;

use think\facade\Db;
use think\Validate;

class ProductValidate extends Validate
{
    protected $rule = [
        'product_name' => 'require|max:100',
        'product_sn' => 'checkUnique',
    ];

    protected $message = [
        'product_name.require' => '商品管理名称不能为空',
        'product_name.max' => '商品管理名称最多100个字符',
        'product_sn.checkUnique' => '商品货号已存在',
    ];

    // 验证唯一
    protected function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['product_id']) ? $data['product_id'] : 0;
        $query = Db::name('product')->where('product_sn', $value)->where('product_id', '<>', $id);
        return $query->count() === 0;
    }
}
