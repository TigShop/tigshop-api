<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员等级
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use think\Model;

class UserRank extends Model
{
    protected $pk = 'rank_id';
    protected $table = 'user_rank';

    //等级类型名称
    const RANK_TYPE_SPECIAL = 1;
    const RANK_TYPE_NORMAL = 2;

    const RANK_TYPE_NAME = [
        self::RANK_TYPE_SPECIAL => '特殊会员组',
        self::RANK_TYPE_NORMAL => '固定等级会员组',
    ];

    //等级类型名称
    public function getRankTypeNameAttr($value, $data)
    {
        return isset($data['rank_type']) && $data['rank_type'] > 0 ? self::RANK_TYPE_NAME[$data['rank_type']] : "";
    }
}
