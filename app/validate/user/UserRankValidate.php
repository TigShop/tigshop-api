<?php
//**---------------------------------------------------------------------+
//** 验证器文件 -- 会员等级
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\validate\user;

use think\facade\Db;
use think\Validate;

class UserRankValidate extends Validate
{
    protected $rule = [
        'rank_name' => 'require|checkUnique|max:100',
    ];

    protected $message = [
        'rank_name.require' => '会员等级名称不能为空',
        'rank_name.max' => '会员等级名称最多100个字符',
        'rank_name.checkUnique' => '会员等级名称已存在',
    ];

    // 验证唯一
    public function checkUnique($value, $rule, $data = [], $field = '')
    {
        $id = isset($data['rank_id']) ? $data['rank_id'] : 0;
        $query = Db::name('user_rank')->where('rank_name', $value)->where('rank_id', '<>', $id);
        return $query->count() === 0;
    }
}
