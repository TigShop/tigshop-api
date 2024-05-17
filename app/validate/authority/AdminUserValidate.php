<?php

namespace app\validate\authority;

use think\facade\Db;
use think\Validate;

class AdminUserValidate extends Validate
{
    protected $rule = [
        'username' => 'require|checkUnique|max:30',
    ];

    protected $message = [
        'username.require' => '管理员名称不能为空',
        'username.max' => '管理员名称最多30个字符',
        'username.checkUnique' => '管理员名称已存在',
    ];

    protected $scene = [
        'create' => [
            'username',
        ],
        'update' => [
            'username',
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
        $id = isset($data['admin_id']) ? $data['admin_id'] : 0;
        $query = Db::name('admin_user')->where('username', $value)->where('admin_id', '<>', $id)->where('store_id', request()->storeId);
        return $query->count() === 0;
    }
}
