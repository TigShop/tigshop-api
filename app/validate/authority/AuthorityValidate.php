<?php

namespace app\validate\authority;

use think\Validate;

class AuthorityValidate extends Validate
{
    protected $rule = [
        'authority_name' => 'require|checkUnique|max:30',
        'authority_sn' => 'require|checkUnique',
    ];

    protected $message = [
        'authority_name.require' => '权限名称不能为空',
        'authority_name.max' => '权限名称最多30个字符',
        'authority_sn.require' => '权限唯一编号不能为空',
        'authority_sn.checkUnique' => '权限唯一编号已存在',
    ];
    // 验证唯一
    protected function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['authority_id']) ? $data['authority_id'] : 0;
        $query = \think\facade\Db::name('authority')->where('authority_sn', $value)->where('admin_type',
            $data['admin_type'])->where('authority_id', '<>', $id);
        return $query->count() === 0;
    }

    protected $scene = [
        'create' => [
            'authority_name',
            'authority_sn'
        ],
        'update' => [
            'authority_name',
            'authority_sn',
        ],
    ];
}
