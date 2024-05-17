<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 供应商
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\authority;

use think\facade\Db;
use think\Validate;

class SuppliersValidate extends Validate
{
    protected $rule = [
        'suppliers_name' => 'require|checkUnique|max:100',
    ];

    protected $message = [
        'suppliers_name.require' => '供应商名称不能为空',
        'suppliers_name.max' => '供应商名称最多100个字符',
        'suppliers_name.checkUnique' => '供应商名称已存在',
    ];

    protected $scene = [
        'create' => [
            'suppliers_name',
        ],
        'update' => [
            'suppliers_name',
        ],
    ];

    /**
     * 验证唯一
     * @param $value
     * @param $rule
     * @param $data
     * @param $field
     * @return bool
     * @throws \think\db\exception\DbException
     */
    protected function checkUnique($value, $rule, $data = [], $field = ''):bool
    {
        $id = isset($data['suppliers_id']) ? $data['suppliers_id'] : 0;
        $query = Db::name('suppliers')->where('suppliers_name', $value)->where('suppliers_id', '<>', $id);
        return $query->count() === 0;
    }
}
