<?php
//**---------------------------------------------------------------------+
//** 模型文件 -- 会员
//**---------------------------------------------------------------------+
//** 版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//** 作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//** 提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\model\user;

use app\common\utils\Time;
use think\Model;

class User extends Model
{
    protected $pk = 'user_id';
    protected $table = 'user';

    // 关联会员等级
    public function userRank()
    {
        return $this->hasOne(UserRank::class, 'rank_id', 'rank_id')->bind(["rank_name", "rank_ico", "discount"]);
    }

    // 关联收货信息
    public function userAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'user_id');
    }

    //来源标签
    const FROM_TAG_WECHAT = 1;
    const FROM_TAG_MINI_PROGRAM = 2;
    const FROM_TAG_H5 = 3;
    const FROM_TAG_PC = 4;
    const FROM_TAG_APP = 5;
    const FROM_TAG = [
        self::FROM_TAG_WECHAT => '公众号',
        self::FROM_TAG_MINI_PROGRAM => '小程序',
        self::FROM_TAG_H5 => 'H5',
        self::FROM_TAG_PC => 'PC',
        self::FROM_TAG_APP => 'APP',
    ];

    //来源标签名称
    public function getFromTagNameAttr($value, $data)
    {
        return self::FROM_TAG[$data['from_tag']] ?? "";
    }

    // 注册日期
    public function getRegTimeAttr($value, $data)
    {
        return Time::format($data['reg_time']);
    }

    // 获取模糊会员名
    public function getDimUsernameAttr($value, $data)
    {
        return $data['username'] ? mb_substr($data['username'], 0, 1, 'utf-8') . '***' . mb_substr($data['username'], -1, 1, 'utf-8') : '';
    }

    // 注册日期检索
    public function scopeRegTime($query, $value)
    {
        if (!empty($value) && is_array($value)) {
            list($start_date, $end_date) = $value;
            $start_date = Time::toTime($start_date);
            $end_date = Time::toTime($end_date) + 86400;
            $value = [$start_date, $end_date];
            return $query->whereTime('reg_time', "between", $value);
        }
    }
}
