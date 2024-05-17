<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 角色管理
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

class AdminRoleValidate extends Validate
{
    protected $rule = [
        'role_name' => 'require|checkUnique|max:100',
    ];

    protected $message = [
        'role_name.require' => '角色管理名称不能为空',
        'role_name.max' => '角色管理名称最多100个字符',
        'role_name.checkUnique' => '角色管理名称已存在',
    ];

    protected $scene = [
        'create' => [
            'role_name',
        ],
        'update' => [
            'role_name',
        ],
    ];

    //验证唯一
    protected function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['role_id']) ? $data['role_id'] : 0;
        $query = Db::name('admin_role')->where('role_name', $value)->where('role_id', '<>', $id);
        return $query->count() === 0;
    }
}
