<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- PC导航栏
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\decorate;

use think\facade\Db;
use think\Validate;

class PcNavigationValidate extends Validate
{
    protected $rule = [
        'title' => 'require|max:100',
        'url' => 'require',
    ];

    protected $message = [
        'title.require' => '名称不能为空',
        'title.max' => '名称最多100个字符',
        'url.require' => '链接地址不能为空',
    ];

    protected $scene = [
        'create' => [
            'title',
        ],
        'update' => [
            'title',
        ],
    ];


    // 验证唯一
    public function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['id']) ? $data['id'] : 0;
        $query = Db::name('pc_navigation')->where($field, $value)->where('id', '<>', $id);
        return $query->count() === 0;
    }
}
